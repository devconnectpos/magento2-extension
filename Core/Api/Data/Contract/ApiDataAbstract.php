<?php

namespace SM\Core\Api\Data\Contract;

use Magento\Framework\App\ObjectManager;
use SM\Core\Model\DataObject;

abstract class ApiDataAbstract extends DataObject
{
    /**
     * get Method
     */
    const GET_METHOD = 'get';
    /**
     * @var []
     */
    protected $dataOutput;

    /**
     * @var []
     */
    protected $allGetApiMethod;

    protected $serializer;

    /**
     * Data as array
     *
     * @return array
     * @throws \ReflectionException
     */
    public function getOutput()
    {
        if (is_null($this->dataOutput)) {
            $methods = $this->getAllGetApiMethod();
            foreach ($methods as $method) {
                if (substr($method, 0, 3) === self::GET_METHOD) {
                    $key                     = $this->underscore(substr($method, 3));
                    $this->dataOutput[$key] = call_user_func_array([$this, $method], []);
                    if ($this->dataOutput[$key] instanceof ApiDataAbstract) {
                        $this->dataOutput[$key] = $this->dataOutput[$key]->getOutput();
                    }
                }
            }
        }

        return $this->dataOutput;
    }

    /**
     * @return array get method
     * @throws \ReflectionException
     */
    public function getAllGetApiMethod()
    {
        if (is_null($this->allGetApiMethod)) {
            $class   = new \ReflectionClass(get_class($this));
            $methods = $class->getMethods(\ReflectionMethod::IS_PUBLIC);
            foreach ($methods as $method) {
                if ($method->getDeclaringClass()->getName() == get_class($this)) {
                    $this->allGetApiMethod[] = $method->getName();
                }
            }
        }

        return $this->allGetApiMethod;
    }

    protected function unserialize($value)
    {
        if (class_exists('\Magento\Framework\Serialize\Serializer\Json')) {
            return $this->getSerialize()->unserialize($value);
        } else {
            return unserialize($value);
        }
    }

    protected function getSerialize()
    {
        if (is_null($this->serializer)) {
            $this->serializer = ObjectManager::getInstance()->create('\Magento\Framework\Serialize\Serializer\Json');
        }

        return $this->serializer;
    }
}
