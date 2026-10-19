<?php

namespace IlBronza\Processes\Helpers\Crud;

use IlBronza\Processes\Helpers\ProcessCreatorHelper;
use IlBronza\Processes\Helpers\ProcessUpdaterHelper;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;
use LogicException;

trait PersistsProcesses
{
    public function bindParameters(array $parameters): Model
    {
        return $this->getModel()->getConnection()->transaction(function () use ($parameters) {
            $attributes = [];
            foreach($this->getFieldsetsProvider()->getBindableAttributeFieldsNames() as $requestName => $attribute)
                if($requestName !== 'status_reason' && array_key_exists($requestName, $parameters))
                    $attributes[$attribute] = $parameters[$requestName];

            $options = ['user' => auth()->user(), 'reason' => $parameters['status_reason'] ?? null];

            try {
                $process = $this->getModel()->exists
                    ? ProcessUpdaterHelper::update($this->getModel(), $attributes, $options)
                    : ProcessCreatorHelper::create(array_merge($this->getModel()->getAttributes(), $attributes), $options);
            } catch (LogicException $exception) {
                throw ValidationException::withMessages(['process_status_id' => $exception->getMessage()]);
            }

            $this->setModel($process);
            $this->associateRelationshipsByType($parameters);

            return $process;
        });
    }
}
