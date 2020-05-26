<?php

namespace Payments\Core\DM;

interface TransactionProcessorInterface
{

    public function settle($payment);

    public function cancel($payment);
}
