<?php
/**
 * Created by Nomad
 * Date: 6/12/19
 * Time: 4:10 PM
 */

namespace SM\Eway\Repositories;

use Eway\Rapid;
use Eway\Rapid\Contract\Client;
use Eway\Rapid\Enum\ApiMethod;
use Eway\Rapid\Enum\TransactionType;
use Eway\Rapid\Model\Response\CreateTransactionResponse;
use Eway\Rapid\Service\Logger;
use Exception;
use Magento\Framework\App\RequestInterface;
use Magento\Store\Model\StoreManagerInterface;
use ReflectionException;
use SM\Eway\Helper\Data;
use SM\XRetail\Helper\DataConfig;
use SM\XRetail\Repositories\Contract\ServiceAbstract;

class EwayIntegrationManagement extends ServiceAbstract
{

    protected $apiKey;

    protected $apiPassword;

    protected $apiEndpoint;
    /**
     * @var Data
     */
    protected $eWayHelper;

    /**
     * EwayIntegrationManagement constructor.
     *
     * @param RequestInterface      $requestInterface
     * @param DataConfig            $dataConfig
     * @param StoreManagerInterface $storeManager
     * @param Data                  $eWayHelper
     */
    public function __construct(
        RequestInterface $requestInterface,
        DataConfig $dataConfig,
        StoreManagerInterface $storeManager,
        Data $eWayHelper
    ) {
        $this->eWayHelper = $eWayHelper;
        parent::__construct($requestInterface, $dataConfig, $storeManager);
    }

    /**
     * @return array
     * @throws ReflectionException
     * @throws Exception
     */
    public function doTransaction()
    {
        if (!$this->eWayHelper->checkEwayRapidSdk()) {
            throw new Exception(
                __('eWAY Rapid SDK is not installed yet. Please run \'composer require eway/eway-rapid-php\' to install eWay Rapid SDK!')
            );
        }

        $data        = $this->getRequest()->getParams();
        $apiConfig   = $data['payment']['payment_data'];
        $cardDetails = $data['cardData'];

        $client = $this->initClient($apiConfig);

        $transactionData = $this->initTransactionData($cardDetails, $data['payment']['amount'], $data['currencyCode']);

        $response = $client->createTransaction(ApiMethod::DIRECT, $transactionData);

        return $this->transactionResult($response);
    }


    /**
     * @return array
     * @throws ReflectionException
     * @throws Exception
     */
    public function doRefund()
    {
        if (!$this->eWayHelper->checkEwayRapidSdk()) {
            throw new Exception(
                __('eWAY Rapid SDK is not installed yet. Please run \'composer require eway/eway-rapid-php\' to install eWay Rapid SDK!')
            );
        }
        $data      = $this->getRequest()->getParams();
        $apiConfig = $data['payment']['payment_data'];
        $client    = $this->initClient($apiConfig);
        $refund    = [
            'Refund' => [
                'TransactionID' => $data['payment']['data']['TransactionID'],
                'TotalAmount'   => abs($data['payment']['refund_amount'])
            ],
        ];

        $response = $client->refund($refund);

        return $this->transactionResult($response);
    }

    /**
     * @param $apiConfig
     *
     * @return Client
     */
    protected function initClient($apiConfig)
    {
        $rapidEndpoint = $apiConfig['sandbox_mode'] ? 'sandbox' : 'production';
        $eWayLogger    = new Logger();

        $client = Rapid::createClient(
            $apiConfig['api_key'],
            $apiConfig['api_password'],
            $rapidEndpoint,
            $eWayLogger
        );

        return $client;
    }

    /**
     * @param $cardDetails
     * @param $paymentAmount
     * @param $currencyCode
     *
     * @return array
     */
    protected function initTransactionData($cardDetails, $paymentAmount, $currencyCode)
    {
        $transaction = [
            'Customer'        => [
                'CardDetails' => [
                    'Name'        => $cardDetails['cardHolderName'],
                    'Number'      => $cardDetails['cardNumber'],
                    'ExpiryMonth' => $cardDetails['cardExpireMonth'],
                    'ExpiryYear'  => $cardDetails['cardExpireYear'],
                    'CVN'         => $cardDetails['cardCVN'],
                ]
            ],
            'Payment'         => [
                'TotalAmount'  => $paymentAmount,
                'CurrencyCode' => $currencyCode,
            ],
            'TransactionType' => TransactionType::PURCHASE,
        ];

        return $transaction;
    }

    /**
     * @param $response
     *
     * @return array
     * @throws ReflectionException
     */
    protected function transactionResult($response)
    {
        if (isset($response->TransactionStatus) && $response->TransactionStatus) {
            $items = [];
            if (isset($response->TransactionType)) {
                $item['TransactionType'] = $response->TransactionType;
            } else {
                $item['TransactionType'] = 'Refund';
            }
            $item['TransactionID']     = $response->TransactionID;
            $item['TransactionStatus'] = $response->TransactionStatus;
            $item['ResponseCode']      = $response->ResponseCode;
            $item['ResponseMessage']   = Rapid::getMessage($response->ResponseMessage);
            $items[]                   = $item;

            return $this->getSearchResult()
                        ->setItems($items)
                        ->setErrors([])
                        ->setTotalCount(1)
                        ->setLastPageNumber(1)
                        ->getOutput();
        }
        $errors = [];
        if (!$response->getErrors()) {
            $errors[] = __('Sorry, your payment was declined');

        } else {
            foreach ($response->getErrors() as $error) {
                array_push($errors, Rapid::getMessage($error));
            }
        }

        return $this->getSearchResult()
                    ->setItems([])
                    ->setErrors($errors)
                    ->setTotalCount(1)
                    ->setLastPageNumber(1)
                    ->getOutput();
    }
}
