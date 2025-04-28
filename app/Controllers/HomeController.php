<?php

namespace App\Controllers;

use App\Models\User;
use Core\Http\Attributes\Controller;
use core\Http\Attributes\Route;
use Core\Http\Response;

#[Controller(prefix: '/api')]
class HomeController
{
    #[Route(path: '/index', method: 'GET')]
    public function index(): Response
    {
        return new Response('Welcome to MyFramework');
    }

    #[Route(path:'/users', method:'GET')]
    public function users(): Response
    {
        $users = User::query()->where('id','=',1)->get();
        return Response::json($users);
    }

//    #[Route('/user/{id}', 'GET')]
//    public function showUser(Request $request, int $id): Response
//    {
//        $user = User::find($id);
//        return Response::json($user ?: ['error' => 'User not found']);
//    }
//
//    #[Route('/create-user', 'POST')]
//    #[Middleware(['auth'])]
//    public function createUser(Request $request): Response
//    {
//        $user = new User();
//        $user->username = $request->post('username');
//        $user->email = $request->post('email');
//        $user->save();
//
//        return Response::json($user, 201);
//    }
}