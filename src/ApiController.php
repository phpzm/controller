<?php

namespace Simples\Controller;

use Simples\Data\Record;
use Simples\Http\Controller;
use Simples\Http\Response;
use Simples\Kernel\App;
use Simples\Model\Repository\ModelRepository;
use Simples\Persistence\Field;
use Simples\Persistence\Filter;

/**
 * Class ApiController
 * @package Simples\Controller
 */
abstract class ApiController extends Controller
{
    /**
     * @var ModelRepository
     */
    protected $repository;

    /**
     * @param null $content
     * @param array $meta
     * @param int $code
     * @return Response
     */
    protected function answer($content = null, $meta = [], $code = 200): Response
    {
        return $this
            ->response()
            ->api($content, $code, $meta);
    }

    /**
     * @return Response
     */
    public function post(): Response
    {
        $this->setLog($this->request()->get('log'));

        $data = $this->getData();

        $posted = $this->repository->create(Record::make($data));

        return $this->answerOK($posted->all());
    }

    /**
     * @return Response
     */
    public function search()
    {
        $this->setLog($this->request()->get('log'));

        $page = (int)coalesce($this->request()->get('page'), 1);
        $size = (int)coalesce($this->request()->get('size'), 25);
        $start = ($page - 1) * $size;
        $end = $size;
        $order = $this->request()->get('order');
        if ($order && !is_array($order)) {
            $order = explode(',', $order);
        }
        $filter = [];

        $data = $this->getData();
        if (count($data)) {
            $filter[] = [
                'separator' => __AND__,
                'filter' => $data
            ];
        }

        $fast = $this->fast($this->request()->get('fast'));
        if (count($fast)) {
            $filter[] = [
                'separator' => __OR__,
                'filter' => $fast
            ];
        }

        $collection = $this->repository->search($filter, $order, $start, $end);
        $meta = ['total' => $this->repository->count($data)];

        if ($collection->size()) {
            return $this->answerOK($collection->getRecords(), $meta);
        }
        return $this->answerNoContent([], $meta);
    }

    /**
     * @param $id
     * @return Response
     */
    public function get($id): Response
    {
        $this->setLog($this->request()->get('log'));

        $data = [$this->repository->getHashKey() => $id];

        $collection = $this->repository->read(Record::make($data));
        if ($id && $collection->size() === 0) {
            return $this->answerGone("The resource `{$id}` was not found");
        }

        return $this->answerOK($collection->getRecords());
    }

    /**
     * @param $id
     * @return Response
     */
    public function put($id): Response
    {
        $this->setLog($this->request()->get('log'));

        $data = $this->getData();
        $data[$this->repository->getHashKey()] = $id;

        $putted = $this->repository->update(Record::make($data));

        return $this->answerOK($putted->all());
    }

    /**
     * @param $id
     * @return Response
     */
    public function delete($id): Response
    {
        $this->setLog($this->request()->get('log'));

        $data = [
            $this->repository->getHashKey() => $id
        ];

        $deleted = $this->repository->destroy(Record::make($data));

        return $this->answerOK($deleted->all());
    }

    /**
     * @return array
     */
    private function getData(): array
    {
        $fields = $this->repository->getFields();

        $data = [];
        /** @var Field $field */
        foreach ($fields as $name => $field) {
            $value = $this->input($name, $field->getType());
            if (!is_null($value)) {
                $data[$name] = $value;
            }
        }
        return $data;
    }

    /**
     * @param $string
     * @return array
     */
    private function fast($string): array
    {
        $peaces = explode(App::options('filter'), $string);
        if (!isset($peaces[1])) {
            return [];
        }
        $fields = $this->repository->getFields();
        $data = [];
        $term = $peaces[0];
        $filters = explode('+', $peaces[1]);
        foreach ($filters as $filter) {
            if (!isset($fields[$filter])) {
                continue;
            }
            /** @var Field $field */
            $field = $fields[$filter];
            switch ($field->getType()) {
                case Field::TYPE_STRING:
                case Field::TYPE_TEXT:
                    $data[$filter] = Filter::apply(Filter::RULE_NEAR, $term);
                    break;
                default:
                    $data[$filter] = $term;
            }
        }

        return $data;
    }
}
