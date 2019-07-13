<?php
/**
 * Created by mr.vjcspy@gmail.com - khoild@smartosc.com.
 * Date: 15/12/2016
 * Time: 17:11
 */

namespace SM\Core\Api\Data;


use SM\Core\Api\Data\Contract\ApiDataAbstract;

class MediaLibrary extends ApiDataAbstract {

    public function getId()
    {
        return $this->getData('id');
    }

    public function getName()
    {
        return $this->getData('name');
    }

    public function getUrl()
    {
        return $this->getData('url');
    }

    public function getIsActive()
    {
        return $this->getData('is_active') == 1;
    }

    public function getType()
    {
        return $this->getData('type');
    }

    public function getCreatedAt()
    {
        return $this->getData('created_at');
    }

    public function getUpdatedAt()
    {
        return $this->getData('updated_at');
    }

}

