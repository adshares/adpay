<?php declare(strict_types = 1);

namespace Adshares\AdPay\Application\DTO;

use Adshares\AdPay\Application\Exception\ValidationDTOException;
use Adshares\AdPay\Domain\Model\ViewEvent;
use Adshares\AdPay\Domain\Model\ViewEventCollection;
use Adshares\AdPay\Lib\DateTimeHelper;
use Adshares\AdPay\Lib\Exception\DateTimeException;
use DateTimeInterface;

final class ViewEventUpdateDTO
{
    /* @var DateTimeInterface */
    private $timeStart;

    /* @var DateTimeInterface */
    private $timeEnd;

    /* @var ViewEventCollection */
    private $viewEvents;

    public function __construct(array $input)
    {
        $this->validate($input);

        $collection = new ViewEventCollection();
        foreach ($input['events'] as $event) {
            $collection->add($this->createEventModel($event));
        }
        $this->viewEvents = $collection;

        try {
            $this->timeStart = DateTimeHelper::createFromTimestamp($input['time_start']);
            $this->timeEnd = DateTimeHelper::createFromTimestamp($input['time_end']);
        } catch (DateTimeException $exception) {
            throw new ValidationDtoException($exception->getMessage());
        }
    }

    private function createEventModel(array $input): ViewEvent
    {
//        try {
//            $eventId = new Id($input['id']);
//
//            return new ViewEvent(
//                $eventId
//            );
//        } catch (InvalidArgumentException|DateTimeException|TypeError $exception) {
//            throw new ValidationDtoException($exception->getMessage());
//        }
        return null;
    }

    private function validate(array $input): void
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
            if (!isset($event['id'])) {
                throw new ValidationDTOException('Field `id` is required.');
            }
//
//            if (!isset($campaign['advertiser_id'])) {
//                throw new ValidationDTOException('Field `advertiser_id` is required.');
//            }
//
//            if (!isset($campaign['time_start'])) {
//                throw new ValidationDTOException('Field `time_start` is required.');
//            }
//
//            if (!isset($campaign['budget'])) {
//                throw new ValidationDTOException('Field `budget` is required.');
//            }
//
//            if (!isset($campaign['banners']) || empty($campaign['banners'])) {
//                throw new ValidationDTOException('Field `banners` is required.');
//            }
//
//            if (!is_array($campaign['banners'])) {
//                throw new ValidationDTOException('Field `banners` must be an array.');
//            }
//
//            $this->validateBanners($campaign['banners']);
//
//            if (isset($campaign['filters'])) {
//                if (!is_array($campaign['filters'])) {
//                    throw new ValidationDTOException('Field `filters` must be an array.');
//                }
//
//                $this->validateFilters($campaign['filters']);
//            }
//
//            if (isset($campaign['conversions'])) {
//                if (!is_array($campaign['conversions'])) {
//                    throw new ValidationDTOException('Field `conversions` must be an array.');
//                }
//
//                $this->validateConversions($campaign['conversions']);
//            }
        }
    }

    public function getViewEvents(): ViewEventCollection
    {
        return $this->viewEvents;
    }

    public function getTimeStart(): DateTimeInterface
    {
        return $this->timeStart;
    }

    public function getTimeEnd(): DateTimeInterface
    {
        return $this->timeEnd;
    }
}
