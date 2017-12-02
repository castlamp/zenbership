<?php

class subupdown {

    private $cart;
    private $sub;
    private $subscription;
    private $price_difference;
    private $options;
    private $id;
    private $direction;
    private $newOption;
    public $error = false;
    public $errorMsg;
    public $charge;
    public $final_price;
    public $new_level;

    public function __construct($id, $direction)
    {
        $this->cart = new cart();
        $this->sub = new subscription();

        $this->id = $id;
        $this->direction = $direction;

        $this->run();
    }

    protected function run()
    {
        $get = $this->subscription = $this->sub->get_subscription($this->id);
        // $getProduct = $this->cart->get_product($get['data']['product']);
        $options = $this->options = $this->cart->get_product_options_all($get['data']['product']);

        $selectedOptions = json_decode($get['data']['product_options'], true);

        $totalOptions = sizeof($options);

        $changes = array();

        $selectedOption = 0;
        $currentSelected = 1;
        $options = array_reverse($options);
        foreach ($options as $aOption) {
            $price = $get['product']['price'] + $aOption['price_adjust'];
            if ($aOption['id'] == $selectedOptions['id']) {
                $selected = true;
                $selectedOption = $currentSelected;
            } else {
                $selected = false;
            }

            $changes[] = array(
                'selected' => $selected,
                'price' => $price,
                'option' => $currentSelected,
                'price_adjust' => $aOption['price_adjust'],
                'raw' => $aOption,
            );

            $currentSelected++;
        }

        $selectedOption--;

        if ($selectedOption == 0) {
            $upgradeOption = $changes['1'];
            $downgradeOption = array();
        } elseif ($selectedOption == $totalOptions-1) {
            $upgradeOption = array();
            $downgradeOption = $changes[$totalOptions-2];
        } else {
            $upgradeOption = $changes[$selectedOption+1];
            $downgradeOption = $changes[$selectedOption-1];
        }

        if ($this->direction == 'down') {
            if ($currentSelected == 1) {
                $this->error = true;
                $this->errorMsg = 'Subscription cannot be downgraded.';
                return $this;
            } else {
                $this->new_level = $newLevel = $currentSelected - 1;
            }
        } else {
            if ($currentSelected == $totalOptions) {
                $this->error = true;
                $this->errorMsg = 'Subscription cannot be upgraded.';
                return $this;
            } else {
                $this->new_level = $newLevel = $currentSelected + 1;
            }
        }

        $arrayIndex = $newLevel - 1;
        $new_price_adjust = $options[$arrayIndex]['price_adjust'];

        if ($this->direction == 'down') {
            $new_price = $this->price_difference = $downgradeOption['price_adjust'] - $selectedOptions['price_adjust'];
            $this->final_price = $final_price = $downgradeOption['price'];
            $this->newOption = $downgradeOption['raw'];
        } else {
            $new_price = $this->price_difference = $upgradeOption['price_adjust'] - $selectedOptions['price_adjust'];
            $this->final_price = $final_price = $upgradeOption['price'];
            $this->newOption = $upgradeOption['raw'];

            // Charge the difference
            $card = $this->cart->get_card($get['data']['card_id']);
            if (! empty($card['gateway'])) {
                $gateway = new $card['gateway']($new_price, $card);
                $this->charge = $gateway->charge();
            }
        }

        $this->cart->general_edit('ppSD_subscriptions', array(
            'price' => $final_price,
            'product_options' => json_encode($this->newOption),
        ), $get['data']['id']);

        $this->sendEmail();

        return $this;
    }

    protected function sendEmail()
    {
        if ($this->direction == 'down') {
            $use_difference = $this->price_difference * -1;
        } else {
            $use_difference = $this->price_difference;
        }

        $changes = array(
            'subscription'   => $this->subscription['data'],
            'product'        => $this->subscription['product'],
            'order_price'    => $use_difference,
            'new_price'      => place_currency($this->final_price),
            'options'        => $this->newOption,
        );

        $email = new email(
            '',
            $this->subscription['data']['member_id'],
            $this->subscription['data']['member_type'],
            '',
            $changes,
            'cart_subscription_changed'
        );

        return true;
    }

    public function getNewPrice()
    {
        return $this->final_price;
    }

}