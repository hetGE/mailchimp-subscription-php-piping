mailchimp-subscription-piping-php
=================================
Subscribe new users automatically to your MailChimp[*][MC] list by using e-mail piping
 
Work flow
---------
* Processes the received e-mail message and parses the name, surname and from address.
* Registers the user to the newsletter by sending the data via MailChimp API.
* Writes log files mailchimp.log and emails.log (optional, see config) for diagnostics.


Important Notice
----------------
The first line has to point to the PHP command line on the server, something like:
`#!/usr/bin/php -q` or `#!/usr/local/bin/php -q`

Immediately after this line, `<?php` tag should start. This is to ensure that the
file does not have any output. Make sure that there aren't any extra spaces and 
line feeds outside `<?php ... ?>`, and also avoid using output functions like 
`echo()` or `var_dump()`.

Otherwise, even though the script will work as expected,
the sender of the e-mail will receive an e-mail indicating that his message
was not delivered along with the output of the PHP script.
You definitely don't want that.


This script uses the following:
-------------------------------
* [PlancakeEmailParser][PcEP] by Danyuki Software Limited.
* [MailChimp API 1.3][MCAPI] and [MCAPI PHP Wrapper 1.3.2][MCAPI-PHP]

--
Coded by [@tolgamorf][me] on Mar 11, 2013.

[PcEP]: https://github.com/plancake/official-library-php-email-parser
[MC]: http://www.mailchimp.com
[MCAPI]: http://apidocs.mailchimp.com/api/1.3/
[MCAPI-PHP]: http://apidocs.mailchimp.com/api/downloads/#php
[me]: https://github.com/tolgamorf
