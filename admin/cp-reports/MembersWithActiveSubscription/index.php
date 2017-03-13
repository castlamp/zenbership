<?php

class MembersWithActiveSubscription extends ContractBasics {

    public function query()
    {
        return "
            SELECT
                ppSD_members.id,
                ppSD_members.email
            FROM
                ppSD_subscriptions
            JOIN
                ppSD_members
                    ON
                        ppSD_members.id=ppSD_subscriptions.member_id
            WHERE
                ppSD_subscriptions.status = '1'
            GROUP BY
                ppSD_subscriptions.member_id
        ";
    }

    public function process(array $data)
    {
        return $data;
    }

}