<?php

namespace Tests\Unit;

use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\AuthController;
use App\Http\Requests\LoginRequest;
use App\Models\User;
use Tests\TestCase;
use Mockery;

class LoginTest extends TestCase
{
    public function test_login_failed_user_not_found(): void
    {
        $mockRequest = Mockery::mock(LoginRequest::class);
        $mockRequest->shouldReceive('all')
            ->andReturn([
                'email' => 'user@email.com',
                'password' => '123'
            ]);

        $userModelMock = Mockery::mock(User::class);

        $userModelMock = Mockery::mock(User::class);
        $userModelMock->shouldReceive('getUserByEmail')
            ->with('user@email.com')
            ->andReturn([]);

        $controller = new AuthController($userModelMock);

        $response = $controller->login($mockRequest);

        $this->assertEquals(404, $response->status());
        $this->assertEquals((object)['success' => false, 'error' => 'Usuário não encontrado!'], $response->getData());
    }

    public function test_login_failed_user_unauthorized(): void
    {
        $userMock = [
            'email' => 'user@email.com',
            'password' => '123'
        ];

        $mockRequest = Mockery::mock(LoginRequest::class);
        $mockRequest->shouldReceive('all')
            ->andReturn($userMock);

        $userModelMock = Mockery::mock(User::class);

        $userModelMock = Mockery::mock(User::class);
        $userModelMock->shouldReceive('getUserByEmail')
            ->with('user@email.com')
            ->andReturn($userMock);

        Auth::shouldReceive('attempt')
            ->with($userMock)
            ->andReturn(false);

        $controller = new AuthController($userModelMock);

        $response = $controller->login($mockRequest);

        $this->assertEquals(401, $response->status());
        $this->assertEquals((object)['success' => false, 'error' => 'Não autorizado!'], $response->getData());
    }

    public function test_login_success(): void
    {
        $userMock = [
            'email' => 'user@email.com',
            'password' => '123'
        ];

        $tokenMock = 'absidj9817312931hkh';

        $mockRequest = Mockery::mock(LoginRequest::class);
        $mockRequest->shouldReceive('all')
            ->andReturn($userMock);

        $userModelMock = Mockery::mock(User::class);

        $userModelMock = Mockery::mock(User::class);
        $userModelMock->shouldReceive('getUserByEmail')
            ->with('user@email.com')
            ->andReturn($userMock);

        Auth::shouldReceive('attempt')
            ->with($userMock)
            ->andReturn($tokenMock);

        $controller = new AuthController($userModelMock);

        $response = $controller->login($mockRequest);

        $this->assertEquals(200, $response->status());
        $this->assertEquals((object)['success' => true, 'data' => (object)['token' => $tokenMock]], $response->getData());
    }
}
