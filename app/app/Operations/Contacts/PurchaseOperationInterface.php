<?php

namespace App\Operations\Contacts;

interface PurchaseOperationInterface
{
    public function purchase($device, $receipt);

    public function detect($subs);
}
