# Add crossells to your Magento order confirmation emails

To use the template in your sales emails, add the following line to your email template:

`{{layout handle="sales_email_crossell" order=$order}}`

Styling may be required in `email-inline.css` to acheive desired layout. Copy `skin/frontend/base/default/css/email-inline.css` to `skin/frontend/YOURTHEME/default/css/email-inline.css` and make the desired changes.