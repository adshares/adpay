<?php

declare(strict_types=1);

namespace Adshares\AdPay\UI\Command;

use Adshares\AdPay\Application\Command\EventDeleteCommand;
use Adshares\AdPay\Application\Command\ReportDeleteCommand;
use DateTimeImmutable;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Command\LockableTrait;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class HistoryClearCommand extends Command
{
    use LockableTrait;

    protected static $defaultName = 'ops:history:clear';

    /** @var ReportDeleteCommand */
    private $paymentReportDeleteCommand;

    /** @var EventDeleteCommand */
    private $eventDeleteCommand;

    public function __construct(
        ReportDeleteCommand $paymentReportDeleteCommand,
        EventDeleteCommand $eventDeleteCommand,
        string $name = null
    ) {
        parent::__construct($name);
        $this->paymentReportDeleteCommand = $paymentReportDeleteCommand;
        $this->eventDeleteCommand = $eventDeleteCommand;
    }

    protected function configure()
    {
        $this
            ->setDescription('Clears historical payments and events')
            ->addOption('before', 'b', InputOption::VALUE_REQUIRED, 'Maximum date to be remove')
            ->addOption('period', 'p', InputOption::VALUE_REQUIRED, 'Period to be remove', 'PT48H');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        if (!$this->lock()) {
            $io->warning('The command is already running in another process.');

            return 1;
        }

        $period = $input->getOption('period');
        $before = $input->getOption('before');

        try {
            if ($before !== null) {
                $dateTo = new DateTimeImmutable((string)$before);
            } else {
                $dateTo = (new DateTimeImmutable())->sub(new \DateInterval((string)$period));
                $dateTo = $dateTo->setTime((int)$dateTo->format('H'), 0);
            }
        } catch (\Exception $e) {
            $io->error($e->getMessage());
            $this->release();

            return 1;
        }

        $io->comment(sprintf('Clearing payments and events older than %s', $dateTo->format('c')));

        $count = $this->paymentReportDeleteCommand->execute($dateTo);
        $io->success(sprintf('%d payment reports removed.', $count));
        $count = $this->eventDeleteCommand->execute($dateTo);
        $io->success(sprintf('%d events removed.', $count));

        $this->release();

        return 0;
    }
}
