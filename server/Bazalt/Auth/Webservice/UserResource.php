<?php

namespace Bazalt\Auth\Webservice;
use Bazalt\Auth\Model\Role;
use Bazalt\Auth\Model\User;
use Bazalt\Data\Validator;
use Tonic\Response;

/**
 * UserResource
 *
 * @uri /auth/users/:id
 */
class UserResource extends \Bazalt\Rest\Resource
{
    /**
     * @method GET
     * @json
     */
    public function getUser($id)
    {
        $user = User::getById($id);
        if (!$user) {
            return new Response(400, ['id' => 'User not found']);
        }
        return new Response(Response::OK, $user->toArray());
    }

    /**
     * @method DELETE
     * @json
     */
    public function deleteUser($id)
    {
        $user = \Bazalt\Auth::getUser();
        $profile = User::getById($id);
        if (!$profile) {
            return new Response(400, ['id' => 'User not found']);
        }
        if (!$user->hasPermission('auth.can_delete_user')) {
            return new Response(Response::FORBIDDEN, 'Permission denied');
        }
        if (!$user->isGuest() && $user->id == $profile->id) {
            return new Response(Response::BADREQUEST, ['id' => 'Can\'t delete yourself']);
        }
        $profile->is_deleted = 1;
        $profile->save();
        return new Response(Response::OK, true);
    }

    /**
     * @method PUT
     * @json
     */
    public function saveUser()
    {
        $data = Validator::create((array)$this->request->data);

        $emailField = $data->field('email')->required()->email();

        $user = User::getById($data['id']);
        if (!$user) {
            return new Response(400, ['id' => 'User not found']);
        }

        $userRoles = [];
        $data->field('roles')->validator('validRoles', function($roles) use (&$userRoles) {
            foreach ($roles as $role) {
                $userRoles[$role] = Role::getById($role);
                if (!$userRoles[$role]) {
                    return false;
                }
            }
            return true;
        }, 'Invalid roles');

        $data->field('login')->required();
        $data->field('gender')->required();

        if (!$data->validate()) {
            return new Response(400, $data->errors());
        }

        $user->login = $data['login'];
        $user->email = $data['email'];
        $user->firstname = $data['firstname'];
        $user->secondname = $data['secondname'];
        $user->patronymic = $data['patronymic'];
        $user->password = User::cryptPassword($data['password']);
        $user->gender = $data['gender'];
        $user->is_active = $data['is_active'];
        $user->is_deleted = $data['is_deleted'];
        $user->save();

        $user->Roles->clearRelations(array_keys($userRoles));
        foreach ($userRoles as $role) {
            $user->Roles->add($role, ['site_id' => 6]);
        }
        return new Response(200, $user->toArray());
    }
}