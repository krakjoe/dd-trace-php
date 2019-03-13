<?php

namespace DDTrace\Integrations\Eloquent;

use DDTrace\Integrations\Integration;
use DDTrace\Integrations\AbstractIntegration;
use DDTrace\Tag;
use DDTrace\Type;
use DDTrace\Util\TryCatchFinally;
use DDTrace\GlobalTracer;

class EloquentIntegration extends AbstractIntegration
{
    const NAME = 'eloquent';

    /**
     * @return string The integration name.
     */
    public function getName()
    {
        return self::NAME;
    }

    public static function load()
    {
        // getModels($columns = ['*'])
        dd_trace('Illuminate\Database\Eloquent\Builder', 'getModels', function () {
            $args = func_get_args();
            $scope = GlobalTracer::get()->startActiveSpan('eloquent.get');
            $span = $scope->getSpan();
            $sql = $this->getQuery()->toSql();
            $span->setTag(Tag::RESOURCE_NAME, $sql);
            $span->setTag(Tag::DB_STATEMENT, $sql);
            $span->setTag(Tag::SPAN_TYPE, Type::SQL);

            return TryCatchFinally::executePublicMethod($scope, $this, 'getModels', $args);
        });

        // performInsert(Builder $query)
        dd_trace('Illuminate\Database\Eloquent\Model', 'performInsert', function () {
            $args = func_get_args();
            $eloquentQueryBuilder = $args[0];
            $scope = GlobalTracer::get()->startActiveSpan('eloquent.insert');
            $span = $scope->getSpan();
            $sql = $eloquentQueryBuilder->getQuery()->toSql();
            $span->setTag(Tag::RESOURCE_NAME, $sql);
            $span->setTag(Tag::DB_STATEMENT, $sql);
            $span->setTag(Tag::SPAN_TYPE, Type::SQL);

            return TryCatchFinally::executeAnyMethod($scope, $this, 'performInsert', $args);
        });

        // performUpdate(Builder $query)
        dd_trace('Illuminate\Database\Eloquent\Model', 'performUpdate', function () {
            $args = func_get_args();
            $eloquentQueryBuilder = $args[0];
            $scope = GlobalTracer::get()->startActiveSpan('eloquent.update');
            $span = $scope->getSpan();
            $sql = $eloquentQueryBuilder->getQuery()->toSql();
            $span->setTag(Tag::RESOURCE_NAME, $sql);
            $span->setTag(Tag::DB_STATEMENT, $sql);
            $span->setTag(Tag::SPAN_TYPE, Type::SQL);

            return TryCatchFinally::executeAnyMethod($scope, $this, 'performUpdate', $args);
        });

        // public function delete()
        dd_trace('Illuminate\Database\Eloquent\Model', 'delete', function () {
            $scope = GlobalTracer::get()->startActiveSpan('eloquent.delete');
            $scope->getSpan()->setTag(Tag::SPAN_TYPE, Type::SQL);

            return TryCatchFinally::executePublicMethod($scope, $this, 'delete', []);
        });

        return Integration::LOADED;
    }
}
