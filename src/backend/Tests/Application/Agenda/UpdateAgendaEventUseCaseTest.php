<?php

declare(strict_types=1);

namespace App\Tests\Application\Agenda;

use PHPUnit\Framework\TestCase;
use App\Application\Agenda\UseCase\UpdateAgendaEventUseCase;
use App\Domain\Agenda\Repository\AgendaRepositoryInterface;
use App\Domain\Agenda\Entity\AgendaEvent;
use App\Domain\Agenda\ValueObject\AgendaId;
use App\Tests\Domain\Agenda\Builder\AgendaEventBuilder;

final class UpdateAgendaEventUseCaseTest extends TestCase
{
    public function test_it_updates_agenda_event_successfully(): void
    {
        $input = [
            'id' => '019e7223-646d-71d1-abee-2ed1613c1d0e',
            'titol' => 'Reunió actualitzada',
            'descripcio' => 'Nova descripció',
            'tipus' => 'reunio',
            'estat' => 'confirmat',
            'lloc' => 'Barcelona',
            'ciutat_id' => '019e7223-646d-71d1-abee-2ed1613c1d0e',
            'data_inici' => '2026-06-10 10:00:00',
            'data_fi' => '2026-06-10 11:00:00',
            'tot_el_dia' => false,
        ];

        $id = AgendaId::fromString($input['id']);

        $existing = AgendaEventBuilder::new()->build();

        $repository = $this->createMock(AgendaRepositoryInterface::class);

        $repository->expects($this->once())
            ->method('findById')
            ->with($this->isInstanceOf(AgendaId::class))
            ->willReturn($existing);

        $repository->expects($this->once())
            ->method('save')
            ->with($this->isInstanceOf(AgendaEvent::class));

        $useCase = new UpdateAgendaEventUseCase($repository);

        $result = $useCase->execute($id, $input);

        $this->assertIsString($result);
    }

    public function test_it_fails_when_event_not_found(): void
    {
        $this->expectException(\RuntimeException::class);

        $input = [
            'id' => '019e7223-646d-71d1-abee-2ed1613c1d0e',
            'titol' => 'Test',
            'tipus' => 'reunio',
            'estat' => 'confirmat',
            'data_inici' => '2026-06-10 10:00:00',
        ];

        $id = AgendaId::fromString($input['id']);

        $repository = $this->createMock(AgendaRepositoryInterface::class);

        $repository->expects($this->once())
            ->method('findById')
            ->with($this->isInstanceOf(AgendaId::class))
            ->willReturn(null);

        $useCase = new UpdateAgendaEventUseCase($repository);

        $useCase->execute($id, $input);
    }
}
