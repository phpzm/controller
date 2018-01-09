<?php

namespace Simples\Controller;

use Simples\Http\Controller;
use Simples\Http\Response;
use Simples\Persistence\QueryBuilder;

/**
 * Class QueryController
 * @package Simples\Controller
 */
abstract class QueryController extends Controller
{
    /**
     * @var QueryBuilder
     */
    protected $queryBuilder;

    /**
     * @inheritDoc
     */
    public function __construct(QueryBuilder $queryBuilder)
    {
        $this->queryBuilder = $queryBuilder;
    }

    /**
     * @param string $source
     * @param string $key
     * @return string
     */
    protected function notDestroyed(string $source, string $key = '_destroyed_at')
    {
        return "({$this->queryBuilder->getDriver()::blank("`{$source}`.`{$key}`")})";
    }

    /**
     * @param string $alias
     * @param string $source
     * @param string $field
     * @param string $where
     * @param string $group
     * @return string
     */
    protected function subQuery($alias, $source, $field, $where = null, $group = null)
    {
        $pieces = [];
        $pieces[] = "(";
        $pieces[] = "SELECT {$field}";
        $pieces[] = "FROM `{$source}` AS `{$alias}`";
        if ($where) {
            $pieces[] = "WHERE {$where}";
        }
        if ($group) {
            $pieces[] = "GROUP BY {$group}";
        }
        $pieces[] = ")";
        return implode(' ', $pieces);
    }

    /**
     * @param mixed $content (null)
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
}
