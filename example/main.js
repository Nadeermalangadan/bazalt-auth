define(['angular', 'angular-resource', 'bazalt-auth'], function(angular) {return angular.module('app', ['bazalt-auth', 'ngResource'])    .config(['$routeProvider', '$locationProvider', '$httpProvider', 'baConfigProvider',     function($routeProvider,   $locationProvider,   $httpProvider,   baConfigProvider){        $locationProvider                         .hashPrefix('!')        //                 .html5Mode(true);        baConfigProvider.baseUrl('/user')                        .apiEndpoint('/example/rest.php/user');        $routeProvider        .when('/', {            template: 'Auth',                controller: function() {            },            access: baConfigProvider.$levels.admin        })        /*.otherwise({            redirectTo: '/'        });*/        $httpProvider.responseInterceptors.push('errorHttpInterceptor');    }])});