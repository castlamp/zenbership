<html xmlns="http://www.w3.org/1999/html">
<head>
    <title>Invoice No. %invoice:id%</title>
    <link href="%theme_url%/css/invoice_style.css" rel="stylesheet" type="text/css"/>
</head>
<body>

<div class="holder">

    %invoice:stamp%

    <div id="logo">%logo%</div>

    <table cellspacing="0" cellpadding="0" width="100%" border="0" class="company">
        <tr>
            <td class="left_details">From</td>
            <td class="details">
                <p>%company_address%</p>

                <p class="company_contact">%company_contact%</p>
            </td>
            <td class="details_wide">
                <table cellspacing="0" cellpadding="0" border="0" class="details">
                    <tr>
                        <td class="left">Invoice No.</td>
                        <td>%invoice:id%</td>
                    </tr>
                    <tr>
                        <td class="left">Date Created</td>
                        <td>%invoice:format_date%</td>
                    </tr>
                    <tr>
                        <td class="left">Date Due</td>
                        <td>%invoice:format_due_date% (%invoice:time_to_due_date%)</td>
                    </tr>
                    <tr>
                        <td class="left">Balance Due</td>
                        <td>%pricing:format_due%</td>
                    </tr>
                    <tr>
                        <td class="left">Status</td>
                        <td>%invoice:format_status%</td>
                    </tr>
                    <tr>
                        <td class="left">Options</td>
                        <td><a href="%invoice:payment_link%">Pay this Invoice</a><br/><a href="%invoice:link%">View
                                Invoice Online</a></td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <table cellspacing="0" cellpadding="0" width="100%" border="0" class="company">
        <tr>
            <td class="left_details">Bill To</td>
            <td class="details">
                %billing:company_name%<br/>%billing:contact_name%<br/>%format_billing%
            </td>
            <td class="left_details">Ship To</td>
            <td class="details">
                %shipping:formatted%
            </td>
        </tr>
    </table>

    <table cellspacing="0" cellpadding="0" width="100%" border="0" class="company">
        <tr>
            <td class="left_details">Memo</td>
            <td class="details_triple">%billing:memo%</td>
        </tr>
    </table>

    <table cellspacing="0" cellpadding="0" width="100%" border="0" class="comps">
        <thead>
        <th>Item</th>
        <th width="100">Qty / Rate</th>
        <th width="120">Unit Price</th>
        <th width="150">Total</th>
        </thead>
        %components%
        <tr class="bottom_row">
            <td colspan="3" class="right">Subtotal</td>
            <td>%pricing:format_subtotal%</td>
        </tr>
        <tr class="bottom_row">
            <td colspan="3" class="right">Tax</td>
            <td>%pricing:format_tax% (%invoice:tax_rate%%)</td>
        </tr>
        <tr class="bottom_row">
            <td colspan="3" class="right">Shipping</td>
            <td>%pricing:format_shipping%</td>
        </tr>
        <tr class="bottom_row">
            <td colspan="3" class="right">Credits</td>
            <td>(%pricing:format_credits%)</td>
        </tr>
        <tr class="bottom_row">
            <td colspan="3" class="right">Payments</td>
            <td>(%pricing:format_paid%)</td>
        </tr>
        <tr class="bottom_row">
            <td colspan="3" class="right strong"><b>Balance Due</b></td>
            <td class="strong">%pricing:format_due%</td>
        </tr>
    </table>

</div>

</body>
</html>