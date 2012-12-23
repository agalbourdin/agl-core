<?php
namespace Agl\Core\Mongo\Query;

/**
 * Create a group of conditions.
 *
 * @category Agl_Core
 * @package Agl_Core_Mongo_Query
 * @version 0.1.0
 * @todo NULL condition
 */

class Conditions
    extends \Agl\Core\Db\Query\Conditions\ConditionsAbstract
        implements \Agl\Core\Db\Query\Conditions\ConditionsInterface
{
    /**
     * MongoDB conditions operators.
     */
    const OPERATOR_NOTEQ  = '$ne';
    const OPERATOR_LT     = '$lt';
    const OPERATOR_LTEQ   = '$lte';
    const OPERATOR_GT     = '$gt';
    const OPERATOR_GTEQ   = '$gte';
    const OPERATOR_IN     = '$in';
    const OPERATOR_NOTIN  = '$nin';
    const OPERATOR_REGEX  = '$regex';
    const OPERATOR_AND    = '$and';
    const OPERATOR_OR     = '$or';
    const OPERATOR_NULL   = '$nexists';
    const OPERATOR_NOTNULL   = '$exists';

    /**
     * Accepted conditions types
     *
     * @todo Update types
     */
    const EQUAL    = '';
    const NOTEQUAL = '$ne';
    const LT       = '$lt';
    const LTEQ     = '$lte';
    const GT       = '$gt';
    const GTEQ     = '$gte';
    const IN       = '$in';
    const NOTIN    = '$nin';
    const REGEX    = '$regex';
    const NULL     = '$nexists';
    const NOTNULL  = '$exists';
    const INSET    = '';
}
