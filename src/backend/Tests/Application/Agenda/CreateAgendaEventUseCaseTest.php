<?php

declare(strict_types=1);

namespace App\Tests\Application\Agenda;

use PHPUnit\Framework\TestCase;
use App\Application\Agenda\UseCase\CreateAgendaEventUseCase;
use App\Domain\Agenda\Repository\AgendaRepositoryInterface;
use App\Domain\Agenda\Entity\AgendaEvent;
use App\Application\Agenda\Factory\AgendaEventFactory;

final class CreateAgendaEventUseCaseTest extends TestCase
{
    public function test_it_creates_agenda_event_successfully(): void
    {
        $input = [
            'titol' => 'Reunió test',
            'descripcio' => 'Desc',
            'tipus' => 'reunio',
            'estat' => 'confirmat',
            'lloc' => 'Barcelona',
            'ciutat_id' => '019e7223-646d-71d1-abee-2ed1613c1d0e',
            'data_inici' => '2026-06-01 10:00:00',
            'data_fi' => '2026-06-01 11:00:00',
            'tot_el_dia' => false,
        ];

        $repository = $this->createMock(AgendaRepositoryInterface::class);

        $repository->expects($this->once())
            ->method('save')
            ->with($this->isInstanceOf(AgendaEvent::class));

        $factory = new AgendaEventFactory();
        $useCase = new CreateAgendaEventUseCase(
            $repository,
            $factory
        );

        $result = $useCase->execute($input);

        $this->assertIsString($result);
        $this->assertNotEmpty($result);
    }

    public function test_it_rejects_invalid_date_range(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $input = [
            'titol' => 'Reunió test',
            'descripcio' => 'Desc',
            'tipus' => 'reunio',
            'estat' => 'confirmat',
            'lloc' => 'Barcelona',
            'ciutat_id' => '019e7223-646d-71d1-abee-2ed1613c1d0e',
            'data_inici' => '2026-06-02 10:00:00',
            'data_fi' => '2026-06-01 10:00:00', // inválido
            'tot_el_dia' => false,
        ];

        $repository = $this->createMock(AgendaRepositoryInterface::class);

        $factory = new AgendaEventFactory();
        $useCase = new CreateAgendaEventUseCase(
            $repository,
            $factory
        );

        $useCase->execute($input);
    }
}
