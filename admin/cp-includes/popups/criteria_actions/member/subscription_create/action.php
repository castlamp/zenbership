<?php

$sub = new subscription();
$cart = new cart();

$product = $cart->get_product($data['product']);

if ($product['type'] != '1') {

    $primaryGateway = $cart->get_gateways('1');

    if (! empty($data['price_override']) && is_numeric($data['price_override'])) {
        $forceTotal = $data['price_override'];
    } else {
        $price = $cart->get_product_price($product['data']['id']);

        $forceTotal = $price['price'];
    }

    if (! empty($data['next_renew'])) {
        $nextRenew = $data['next_renew'];
    } else {
        $nextRenew = '';
    }

    foreach ($ids as $memberId) {
        $cards = $cart->getUserCards($memberId);
        if (! empty($cards['0'])) {
            $cardId = $cards['0']['id'];
            $gateway = $cards['0']['gateway'];
        } else {
            $cardId = '';
            $gateway = $primaryGateway;
        }

        $id = $sub->create_subscription($product, '', $memberId, $cardId, '0', '', $gateway, 'member', $forceTotal, $nextRenew);
    }

} else {
    ajax_error('Product must be a subscription product.');
}