<?php
/**
 * Copyright © MercadoPago. All rights reserved.
 *
 * @author      Bruno Elisei <brunoelisei@o2ti.com>
 * @license     See LICENSE for license details.
 */

namespace MercadoPago\PaymentMagento\Gateway\Response;

use InvalidArgumentException;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Gateway\Response\HandlerInterface;
use Magento\Sales\Model\Order\Payment\Transaction;

/**
 * Gateway response Initialize a Card Payment.
 */
class CcTransactionInitializeHandler implements HandlerInterface
{
    /**
     * Payment Id block name.
     */
    public const PAYMENT_ID = 'id';

    /**
     * Response Pay Credit - Block name.
     */
    public const CREDIT = 'credit';

    /**
     * Response Pay Payment Id - Block name.
     */
    public const RESPONSE_PAYMENT_ID = 'id';

    /**
     * Response Pay Status - Block name.
     */
    public const STATUS = 'status';

    /**
     * Response Pay Approved - Block name.
     */
    public const APPROVED = 'approved';

    /**
     * Response Pay In Process - Block name.
     */
    public const IN_PROCCESS = 'in_process';

    /**
     * Handles.
     *
     * @param array $handlingSubject
     * @param array $response
     *
     * @return void
     */
    public function handle(array $handlingSubject, array $response)
    {
        if (!isset($handlingSubject['payment'])
            || !$handlingSubject['payment'] instanceof PaymentDataObjectInterface
        ) {
            throw new InvalidArgumentException('Payment data object should be provided');
        }
        $isApproved = false;

        $isDenied = true;

        $paymentDO = $handlingSubject['payment'];

        $payment = $paymentDO->getPayment();

        $order = $payment->getOrder();

        $amount = $order->getBaseGrandTotal();

        $transactionId = $response[self::PAYMENT_ID];
        $payment->setTransactionId($transactionId);
        $payment->setIsTransactionPending(1);
        $payment->setIsTransactionClosed(false);
        $payment->setAuthorizationTransaction($transactionId);
        $payment->addTransaction(Transaction::TYPE_AUTH);

        $order = $payment->getOrder();
        $order->setState(\Magento\Sales\Model\Order::STATE_NEW);
        $order->setStatus('pending');
        $comment = __('Awaiting payment of the Card.');
        $order->addStatusHistoryComment($comment, $payment->getOrder()->getStatus());
    }
}
