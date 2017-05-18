<html xmlns="http://www.w3.org/1999/html">
<head>
    <title>Project Quote</title>
    <link href="%theme_url%/css/invoice_style.css" rel="stylesheet" type="text/css"/>
</head>
<body>

<div class="holder">

    <div id="logo">%logo%</div>

    <h1 style="margin-bottom:32px;text-align:center;">Here is your quote!</h1>

    <table cellspacing="0" cellpadding="0" width="100%" border="0" class="company">
        <tr>
            <td class="left_details">Quoting Party</td>
            <td class="details">
                <p>%company_address%</p>

                <p class="company_contact">%company_contact%</p>
            </td>
            <td class="left_details">Quoted Party</td>
            <td class="details">
                <p>Prepared on <b>%invoice:format_date%</b> for:</p>

                <p class="company_contact">%billing:company_name%<br/>%billing:contact_name%<br/>%format_billing%</p>
            </td>
        </tr>
    </table>

    <table cellspacing="0" cellpadding="0" width="100%" border="0" class="company">
        <tr>
            <td class="left_details">Details</td>
            <td class="details_triple">
                %billing:memo%
            </td>
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
            <td colspan="3" class="zen_cart_subtotal right">Tax</td>
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
            <td colspan="3" class="right strong"><b>Quote Total</b></td>
            <td class="strong">%pricing:format_due%</td>
        </tr>
    </table>

</div>

</body>
</html>