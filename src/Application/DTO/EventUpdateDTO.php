<?php declare(strict_types = 1);

namespace Adshares\AdPay\Application\DTO;

use Adshares\AdPay\Application\Exception\ValidationDTOException;
use Adshares\AdPay\Domain\Exception\InvalidArgumentException;
use Adshares\AdPay\Domain\Model\Event;
use Adshares\AdPay\Domain\Model\EventCollection;
use Adshares\AdPay\Domain\Model\Impression;
use Adshares\AdPay\Domain\Model\ImpressionCase;
use Adshares\AdPay\Domain\ValueObject\Context;
use Adshares\AdPay\Domain\ValueObject\Id;
use Adshares\AdPay\Lib\DateTimeHelper;
use Adshares\AdPay\Lib\Exception\DateTimeException;
use DateTimeInterface;
use TypeError;

abstract class EventUpdateDTO
{
    /* @var DateTimeInterface */
    protected $timeStart;

    /* @var DateTimeInterface */
    protected $timeEnd;

    /* @var EventCollection */
    protected $viewEvents;

    public function __construct(array $input)
    {
        $this->validate($input);
        $this->fill($input);
    }

    public function getTimeStart(): DateTimeInterface
    {
        return $this->timeStart;
    }

    public function getTimeEnd(): DateTimeInterface
    {
        return $this->timeEnd;
    }

    public function getEvents(): EventCollection
    {
        return $this->viewEvents;
    }

    protected function validate(array $input): void
    {
        if (!isset($input['time_start'])) {
            throw new ValidationDTOException('Field `events` is required.');
        }

        if (!isset($input['time_end'])) {
            throw new ValidationDTOException('Field `events` is required.');
        }

        if (!isset($input['events'])) {
            throw new ValidationDTOException('Field `events` is required.');
        }

        foreach ($input['events'] as $event) {
            $this->validateEvent($event);
            $this->validateImpressionCase($event);
            $this->validateImpression($event);
        }
    }

    protected function validateEvent(array $input): void
    {
        if (!isset($input['id'])) {
            throw new ValidationDTOException('Field `id` is required.');
        }
        if (!isset($input['time'])) {
            throw new ValidationDTOException('Field `time` is required.');
        }
    }

    protected function validateImpressionCase(array $input): void
    {
        if (!isset($input['case_id'])) {
            throw new ValidationDTOException('Field `case_id` is required.');
        }
        if (!isset($input['publisher_id'])) {
            throw new ValidationDTOException('Field `publisher_id` is required.');
        }
        if (!isset($input['zone_id'])) {
            throw new ValidationDTOException('Field `zone_id` is required.');
        }
        if (!isset($input['advertiser_id'])) {
            throw new ValidationDTOException('Field `advertiser_id` is required.');
        }
        if (!isset($input['campaign_id'])) {
            throw new ValidationDTOException('Field `campaign_id` is required.');
        }
        if (!isset($input['banner_id'])) {
            throw new ValidationDTOException('Field `banner_id` is required.');
        }
    }

    protected function validateImpression(array $input): void
    {
        if (!isset($input['impression_id'])) {
            throw new ValidationDTOException('Field `impression_id` is required.');
        }
        if (!isset($input['tracking_id'])) {
            throw new ValidationDTOException('Field `tracking_id` is required.');
        }
        if (!isset($input['user_id'])) {
            throw new ValidationDTOException('Field `user_id` is required.');
        }
        if (!isset($input['human_score'])) {
            throw new ValidationDTOException('Field `human_score` is required.');
        }
    }

    protected function fill(array $input): void
    {
        try {
            $this->timeStart = DateTimeHelper::createFromTimestamp($input['time_start']);
            $this->timeEnd = DateTimeHelper::createFromTimestamp($input['time_end']);

            if ($this->timeStart > $this->timeEnd) {
                throw new ValidationDtoException('Start time cannot be greater than end time');
            }

            $collection = new EventCollection();
            foreach ($input['events'] as $event) {
                $collection->add($this->createEventModel($event));
            }
            $this->viewEvents = $collection;
        } catch (InvalidArgumentException|DateTimeException|TypeError $exception) {
            throw new ValidationDTOException($exception->getMessage());
        }
    }

    abstract protected function createEventModel(array $input): Event;

    protected function createImpressionCaseModel(array $input): ImpressionCase
    {
        return new ImpressionCase(
            new Id($input['case_id']),
            new Id($input['publisher_id']),
            new Id($input['zone_id']),
            new Id($input['advertiser_id']),
            new Id($input['campaign_id']),
            new Id($input['banner_id']),
            $this->createImpressionModel($input)
        );
    }

    protected function createImpressionModel(array $input): Impression
    {
        return new Impression(
            new Id($input['impression_id']),
            new Id($input['tracking_id']),
            new Id($input['user_id']),
            new Context($input['context'] ?? []),
            $input['human_score']
        );
    }
}
