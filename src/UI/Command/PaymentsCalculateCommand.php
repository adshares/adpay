<?php

declare(strict_types=1);

namespace App\UI\Command;

use App\Application\Command\ReportCalculateCommand;
use App\Application\Command\ReportFetchCommand;
use App\Application\Exception\FetchingException;
use App\Lib\DateTimeHelper;
use App\Lib\Exception\DateTimeException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Command\LockableTrait;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class PaymentsCalculateCommand extends Command
{
    use LockableTrait;

    protected static $defaultName = 'ops:payments:calculate';

    private ReportFetchCommand $reportFetchCommand;

    private ReportCalculateCommand $reportCalculateCommand;

    public function __construct(
        ReportFetchCommand $reportFetchCommand,
        ReportCalculateCommand $reportCalculateCommand,
        string $name = null
    ) {
        parent::__construct($name);
        $this->reportFetchCommand = $reportFetchCommand;
        $this->reportCalculateCommand = $reportCalculateCommand;
    }

    protected function configure()
    {
        $this
            ->setDescription('Calculates payments for events')
            ->addArgument('date', InputArgument::OPTIONAL, 'Report date or timestamp')
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'Force calculation of incomplete report');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        if (!$this->lock()) {
            $io->warning('The command is already running in another process.');

            return self::FAILURE;
        }

        $date = $input->getArgument('date');
        if ($date === null) {
            $this->calculateAll($io);
        } else {
            if (preg_match('/^\d+$/', $date)) {
                $timestamp = (int)$date;
            } else {
                try {
                    $timestamp = (DateTimeHelper::fromString($date)->getTimestamp());
                } catch (DateTimeException $exception) {
                    $io->error($exception->getMessage());
                    $this->release();

                    return self::FAILURE;
                }
            }

            $this->calculate($timestamp, $input->getOption('force'), $io);
        }

        $this->release();

        return self::SUCCESS;
    }

    private function calculateAll(SymfonyStyle $io)
    {
        $dto = $this->reportFetchCommand->execute(false, true, false);
        $ids = $dto->getReportIds();

        $io->comment(sprintf('Found %d complete reports.', count($ids)));
        foreach ($ids as $reportId) {
            $this->calculate($reportId, false, $io);
        }
    }

    private static function getReportInfo(int $timestamp): string
    {
        $interval = 3600;

        $reportId = (int)floor($timestamp / $interval) * $interval;
        $timeStart = DateTimeHelper::fromTimestamp($reportId);
        $timeEnd = DateTimeHelper::fromTimestamp($reportId + $interval - 1);

        return sprintf(
            'Calculating report #%d from %s to %s',
            $reportId,
            $timeStart->format('Y-m-d H:i:s'),
            $timeEnd->format('Y-m-d H:i:s')
        );
    }

    private function calculate(int $timestamp, bool $force, SymfonyStyle $io)
    {
        $io->comment(self::getReportInfo($timestamp));

        try {
            $count = $this->reportCalculateCommand->execute($timestamp, $force);
        } catch (FetchingException $exception) {
            $io->warning($exception->getMessage());

            return;
        }

        $io->success(sprintf('%d payments calculated.', $count));
    }
}
