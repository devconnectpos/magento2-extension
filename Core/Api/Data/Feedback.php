<?php
/**
 * Created by mr.vjcspy@gmail.com - khoild@smartosc.com.
 * Date: 15/12/2016
 * Time: 17:11
 */

namespace SM\Core\Api\Data;


use SM\Core\Api\Data\Contract\ApiDataAbstract;

class Feedback extends ApiDataAbstract {

    public function getId()
    {
        return $this->getData('id');
    }

    public function getRetailId()
    {
        return $this->getData('retail_id');
    }

    public function getRetailFeedback()
    {
        return $this->getData('retail_feedback');
    }

    public function getRetailRate()
    {
        return $this->getData('retail_rate');
    }

}

