<?php declare(strict_types = 1);

namespace Adshares\AdPay\UI\Command;

use Adshares\AdPay\Application\Command\ReportCalculateCommand;
use Adshares\AdPay\Application\Command\ReportFetchCommand;
use Adshares\AdPay\Application\Exception\FetchingException;
use Adshares\AdPay\Lib\DateTimeHelper;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Command\LockableTrait;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use DateTimeInterface;

class PaymentsCalculateCommand extends Command
{
    use LockableTrait;

    protected static $defaultName = 'app:payments:calculate';

    /** @var ReportFetchCommand */
    private $reportFetchCommand;

    /** @var ReportCalculateCommand */
    private $reportCalculateCommand;

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
            ->addArgument('timestamp', InputArgument::OPTIONAL, 'Report time')
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'Force calculation of incomplete report');
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

    private static function getReportInfo(int $timestamp)
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
            $count = $this->reportCalculateCommand->execute((int)$timestamp, $force);
        } catch (FetchingException $exception) {
            $io->warning($exception->getMessage());

            return;
        }

        $io->success(sprintf('%d payments calculated.', $count));
    }

    protected function interact(InputInterface $input, OutputInterface $output)
    {
        parent::interact($input, $output);

        $timestamp = $input->getArgument('timestamp');
        if ($timestamp !== null && !preg_match('/^\d+$/', $timestamp)) {
            throw new \RuntimeException(sprintf('Timestamp "%s" is invalid.', $timestamp));
        }
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);
        $timestamp = $input->getArgument('timestamp');

        if (!$this->lock()) {
            $io->warning('The command is already running in another process.');

            return 0;
        }

        if ($timestamp === null) {
            $this->calculateAll($io);
        } else {
            $this->calculate((int)$timestamp, $input->getOption('force'), $io);
        }

        $this->release();
    }
}
