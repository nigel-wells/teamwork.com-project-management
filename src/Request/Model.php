<?php

namespace TeamWorkPm\Request;

use TeamWorkPm\Exception;
use TeamWorkPm\Helper\Str;

abstract class Model
{
    protected $method = null;
    protected $action = null;
    protected $parent = null;
    protected $fields = [];

    /**
     * @param string $parent
     * @return $this
     */
    public function setParent($parent)
    {
        $this->parent = $parent;
        return $this;
    }

    /**
     * @param string $action
     * @return $this
     */
    public function setAction($action)
    {
        $this->action = $action;
        return $this;
    }

    /**
     * @param array $fields
     * @return $this
     */
    public function setFields(array $fields)
    {
        $this->fields = $fields;
        return $this;
    }

    /**
     * @param string $field
     * @param array $options
     * @param array $parameters
     *
     * @return mixed|null
     * @throws \TeamWorkPm\Exception
     */
    protected function getValue(&$field, &$options, array $parameters)
    {
        static $camelize = [
            'pending_file_attachments' => true,
            'date_format' => true,
            'send_welcome_email' => true,
            'receive_daily_reports' => true,
            'welcome_email_message' => true,
            'auto_give_project_access' => true,
            'open_id' => true,
            'user_language' => true,
            'pending_file_ref' => true,
            'new_company' => true,
        ],
        $yes_no_boolean = [
            'welcome_email_message',
            'send_welcome_email',
            'receive_daily_reports',
            'notes',
            'auto_give_project_access',
        ],
        $preserve = [
            'address_one' => true,
            'address_two' => true,
        ];
        $value = isset($parameters[$field]) ? $parameters[$field] : null;
        if (!is_array($options)) {
            $options = ['required' => $options, 'attributes' => []];
        }
        $isNull = null === $value;
        if ($this->method === 'POST' && $options['required']) {
            if ($isNull) {
                throw new Exception('Required field ' . $field);
            }
        }
        // checking fields that must meet certain values
        if (!$isNull
            && isset($options['validate'])
            && !in_array($value, $options['validate'])
        ) {
            throw new Exception(
                'Invalid value for field ' .
                $field
            );
        }
        // @todo Note that the people at team work do not constantly maintain the name-other format.
        if (isset($camelize[$field])) {
            if ($field === 'open_id') {
                $field = 'openID';
            } else {
                $field = Str::camel($field);
            }
        } elseif (!isset($preserve[$field])) {
            if ($field === 'company_id') {
                if ($this->action === 'projects') {
                    $field = Str::camel($field);
                } elseif ($this->action === 'people') {
                    $field = Str::dash($field);
                }
            } else {
                $field = Str::dash($field);
            }
        }
        return $value;
    }

    protected function actionInclude($value)
    {
        return false !== strrpos($this->action, $value);
    }

    protected function getParent()
    {
        return $this->parent . ($this->actionInclude('/reorder') ? 's' : '');
    }

    /**
     * @param string $method
     * @param string|array|null $parameters
     * @return string|null
     */
    public function getParameters($method, $parameters)
    {
        if ($parameters) {
            $this->method = $method;
            if ($method === 'GET') {
                if (is_array($parameters)) {
                    $parameters = http_build_query($parameters);
                }
            } elseif ($method === 'POST' || $method === 'PUT') {
                $parameters = $this->parseParameters($parameters);
            }
        } else {
            $parameters = null;
        }
        return $parameters;
    }

    /**
     * Return parameters for post and put request
     *
     * @param array $parameters
     * @return string
     */
    abstract protected function parseParameters($parameters);
}
