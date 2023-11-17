<?php

namespace Ngenius\NgeniusCommon\Processor;

class ApiProcessor
{
    private array $response;

    public const NGENIUS_CAPTURE_LITERAL = 'cnp:capture';

    public function __construct(array $response)
    {
        $this->response = $response;
    }

    /**
     * Gets Last Transaction
     *
     * @return string|array
     */
    public function getLastTransaction(): string|array
    {
        $lastTransaction = '';
        if (isset($this->response['_embedded']['payment'][0]['_embedded'][self::NGENIUS_CAPTURE_LITERAL])
            && is_array($this->response['_embedded']['payment'][0]['_embedded'][self::NGENIUS_CAPTURE_LITERAL])
        ) {
            $lastTransaction = end($this->response['_embedded']['payment'][0]
            ['_embedded'][self::NGENIUS_CAPTURE_LITERAL]);
        }
        return $lastTransaction;
    }

    /**
     * Gets payment id
     *
     * @return string
     */
    public function getPaymentId(): string
    {
        $paymentId = '';
        if (isset($this->response['_embedded']['payment'][0]['_id'])) {
            $transactionIdRes = explode(":", $this->response['_embedded']['payment'][0]['_id']);
            $paymentId = end($transactionIdRes);
        }
        return $paymentId;
    }

    /**
     * Gets transaction Id
     *
     * @return string
     */
    public function getTransactionId(): string
    {
        $lastTransaction = $this->getLastTransaction();
        $transactionId = '';
        if (isset($lastTransaction['_links']['self']['href'])) {
            $transactionArr = explode('/', $lastTransaction['_links']['self']['href']);
            $transactionId = end($transactionArr);
        } elseif ($lastTransaction['_links']['cnp:refund']['href'] ?? false) {
            $transactionArr = explode('/', $lastTransaction['_links']['cnp:refund']['href']);
            $transactionId = $transactionArr[count($transactionArr)-2];
        }
        return $transactionId;
    }

    /**
     * Gets Captured Amount
     *
     * @return int|string
     */
    public function getCapturedAmount(): int|string
    {
        $captureAmount = 0;
        if (isset($this->response['_embedded']['payment'][0]['_embedded'][self::NGENIUS_CAPTURE_LITERAL])
            && is_array($this->response['_embedded']['payment'][0]['_embedded'][self::NGENIUS_CAPTURE_LITERAL])
        ) {
            foreach ($this->response['_embedded']['payment'][0]['_embedded']
                     [self::NGENIUS_CAPTURE_LITERAL] as $capture) {
                if (isset($capture['state']) && ($capture['state'] == 'SUCCESS')
                    && isset($capture['amount']['value'])
                ) {
                    $captureAmount = $capture['amount']['value'];
                }
            }
        }
        return $captureAmount;
    }

    /**
     * Get the state of the N-Genius order
     *
     * @return string|null
     */
    public function getState(): ?string
    {
        return $this->response['_embedded']['payment'][0]['state'];
    }


    /**
     * @return array
     */
    public function getResponse(): array
    {
        return $this->response;
    }

    /**
     * @param array $response
     */
    public function setResponse(array $response): void
    {
        $this->response = $response;
    }
}
