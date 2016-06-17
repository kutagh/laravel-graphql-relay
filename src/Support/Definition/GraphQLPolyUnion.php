<?php

namespace Nuwave\Relay\Support\Definition;

use GraphQL\Type\Definition\UnionType;
use Illuminate\Database\Eloquent\Relations\MorphOne;

class GraphQLPolyUnion extends GraphQLType
{

    protected function getTypeResolver()
    {
        if(!method_exists($this, 'resolveType'))
        {
            return null;
        }

        $resolver = array($this, 'resolveType');
        return function() use ($resolver)
        {
            $args = func_get_args();
            $unmorhpedRoot = $args[0];
            $morphedRoot = method_exists($unmorhpedRoot, 'morphTo') && $unmorhpedRoot->morphTo() instanceof MorphOne ? $unmorhpedRoot->morphTo : null;
            $args[0] = $morphedRoot;
            return call_user_func_array($resolver, $args);
        };
    }

    /**
     * Get the attributes from the container.
     *
     * @return array
     */
    public function getAttributes()
    {
        $attributes = parent::getAttributes();

        $resolver = $this->getTypeResolver();
        if(isset($resolver))
        {
            $attributes['resolveType'] = $resolver;
        }

        $types = $this->unionTypes();
        $attributes['types'] = $types;

        return $attributes;
    }

    public function unionTypes() {
        return [];
    }

    public function toType()
    {

        return new UnionType($this->toArray());
    }

}
