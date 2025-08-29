<?php

namespace Tests\Unit;

use App\Http\Controllers\ParticipantController;
use App\Http\Requests\CreateParticipantRequest;
use App\Models\Participant;
use App\Services\CacheService;
use Tests\TestCase;
use Mockery;

class ParticipantControllerTest extends TestCase
{
    public function test_create_participant_error(): void
    {
        $mockRequestData = [
            'name' => 'teste'
        ];

        $mockRequest = Mockery::mock(CreateParticipantRequest::class);
        $mockRequest->shouldReceive('all')
            ->once()
            ->andReturn($mockRequestData);

        $mockCacheService = Mockery::mock(CacheService::class);

        $mockParticipantModel = Mockery::mock(Participant::class);
        $mockParticipantModel->shouldReceive('createParticipant')
            ->once()
            ->with($mockRequestData['name'])
            ->andReturn([]);

        $controller = new ParticipantController($mockParticipantModel, $mockCacheService);


        $response = $controller->createParticipant($mockRequest);

        $this->assertEquals(400, $response->status());
        $this->assertEquals((object)['success' => false, 'error' => 'Participante não foi criado, tente novamente mais tarde!'], $response->getData());
    }

    public function test_create_participant_ok(): void
    {
        $mockRequestData = [
            'name' => 'teste'
        ];

        $mockResponseCreate = [
            'name' => 'teste',
            'id' => 1,
            'created_at' => 'XXXX-XX-XX XX:XX:XX',
            'updated_at' => 'XXXX-XX-XX XX:XX:XX'
        ];

        $mockRequest = Mockery::mock(CreateParticipantRequest::class);
        $mockRequest->shouldReceive('all')
            ->once()
            ->andReturn($mockRequestData);

        $mockCacheService = Mockery::mock(CacheService::class);
        $mockCacheService->shouldReceive('increment')
            ->once()
            ->with('participants', $mockResponseCreate);

        $mockParticipantModel = Mockery::mock(Participant::class);
        $mockParticipantModel->shouldReceive('createParticipant')
            ->once()
            ->with($mockRequestData['name'])
            ->andReturn($mockResponseCreate);

        $controller = new ParticipantController($mockParticipantModel, $mockCacheService);

        $response = $controller->createParticipant($mockRequest);

        $this->assertEquals(200, $response->status());
        $this->assertEquals((object)['success' => true, 'data' => (object) $mockResponseCreate], $response->getData());
    }

    public function test_get_all_participants_no_cache_error(): void
    {
        $mockCacheService = Mockery::mock(CacheService::class);
        $mockCacheService->shouldReceive('read')
            ->once()
            ->with('participants')
            ->andReturn([]);
        $mockCacheService->shouldReceive('create')
            ->with('participants', [], 600);

        $mockParticipantModel = Mockery::mock(Participant::class);
        $mockParticipantModel->shouldReceive('getAllParticipants')
            ->once()
            ->andReturn([]);

        $controller = new ParticipantController($mockParticipantModel, $mockCacheService);

        $response = $controller->getAllParticipants();

        $this->assertEquals(404, $response->status());
        $this->assertEquals((object)['success' => false, 'error' => 'Nenhum participante cadastrado até o momento!'], $response->getData());
    }

    public function test_get_all_participants_no_cache_ok(): void
    {
        $mockReturn = [
            (object)[
                'name' => 'teste',
                'created_at' => 'XXXX-XX-XX XX:XX:XX',
                'updated_at' => 'XXXX-XX-XX XX:XX:XX'
            ]
        ];

        $mockCacheService = Mockery::mock(CacheService::class);
        $mockCacheService->shouldReceive('read')
            ->once()
            ->with('participants')
            ->andReturn([]);

        $mockCacheService->shouldReceive('create')
            ->once()
            ->with('participants', $mockReturn, 600);

        $mockParticipantModel = Mockery::mock(Participant::class);
        $mockParticipantModel->shouldReceive('getAllParticipants')
            ->once()
            ->andReturn($mockReturn);

        $controller = new ParticipantController($mockParticipantModel, $mockCacheService);

        $response = $controller->getAllParticipants();

        $this->assertEquals(200, $response->status());
        $this->assertEquals((object)['success' => true, 'data' => $mockReturn], $response->getData());
    }

    public function test_get_all_participants_with_cache_ok(): void
    {
        $mockReturn = [
            (object)[
                'name' => 'teste',
                'created_at' => 'XXXX-XX-XX XX:XX:XX',
                'updated_at' => 'XXXX-XX-XX XX:XX:XX'
            ]
        ];

        $mockCacheService = Mockery::mock(CacheService::class);
        $mockCacheService->shouldReceive('read')
            ->once()
            ->with('participants')
            ->andReturn($mockReturn);

        $mockParticipantModel = Mockery::mock(Participant::class);

        $controller = new ParticipantController($mockParticipantModel, $mockCacheService);

        $response = $controller->getAllParticipants();

        $this->assertEquals(200, $response->status());
        $this->assertEquals((object)['success' => true, 'data' => $mockReturn], $response->getData());
    }
}
