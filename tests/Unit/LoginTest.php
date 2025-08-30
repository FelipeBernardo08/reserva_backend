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
        $userModelMock = Mockery::mock(User::class);

        $mockRequest->shouldReceive('all')
            ->once()
            ->andReturn([
                'email' => 'user@email.com',
                'password' => '123'
            ]);

        $userModelMock->shouldReceive('getUserByEmail')
            ->once()
            ->with('user@email.com')
            ->andReturn([]);

        $controller = new AuthController($userModelMock);

        $response = $controller->login($mockRequest);

        $this->assertEquals(404, $response->status());
        $this->assertEquals((object)['success' => false, 'error' => 'Usuário não encontrado!'], $response->getData());
    }

    public function test_login_failed_user_unauthorized(): void
    {
        $mockRequest = Mockery::mock(LoginRequest::class);
        $userModelMock = Mockery::mock(User::class);

        $userMock = [
            'email' => 'user@email.com',
            'password' => '123'
        ];

        $mockRequest->shouldReceive('all')
            ->once()
            ->andReturn($userMock);

        $userModelMock->shouldReceive('getUserByEmail')
            ->once()
            ->with('user@email.com')
            ->andReturn($userMock);

        Auth::shouldReceive('attempt')
            ->once()
            ->with($userMock)
            ->andReturn(false);

        $controller = new AuthController($userModelMock);

        $response = $controller->login($mockRequest);

        $this->assertEquals(401, $response->status());
        $this->assertEquals((object)['success' => false, 'error' => 'Não autorizado!'], $response->getData());
    }

    public function test_login_success(): void
    {
        $mockRequest = Mockery::mock(LoginRequest::class);
        $userModelMock = Mockery::mock(User::class);

        $userMock = [
            'email' => 'user@email.com',
            'password' => '123'
        ];

        $tokenMock = 'absidj9817312931hkh';

        $mockRequest->shouldReceive('all')
            ->andReturn($userMock);

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
