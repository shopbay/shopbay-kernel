# Change Log (shopbay-kernel)

## Version 0.25 - Aug 5, 2018

This release contains several enhancements and bug fixes, as well as also supports PHP 7.2 and also Yii 1.1.20 and Yii 2.0.15
 
### New Features:

 + New: Added support to track as many guest accounts for each shop by using guest email address for customer_id.
Registered (sign up) customer will be assigned id start with Account::TYPE_CUSTOMER and one running sequence number, e.g. c1, c2 ... cN, whereas guest account will be assigned email address as their customer id.
When guest account eventually converted (register as shop customer account), all previous orders under guest email address will be auto transferred to newly registered customer account

### Enhancements:

 - Enh: Upgraded to support PHP 7.2
 - Enh: Upgraded to support Yii 1.1.20 and Yii 2.0.15
 - Enh: Added support for Google Site Tag (gtag.js) at extensions.googleAnalytics.GoogleAnalytics
 - Chg: Rename `DummyPaymentGateway` to `TestPaymentGateway` for better semantics and also this test payment gateway will be used in Codeception testing
 - Enh: Added common version footer under `common.views.version.index`
 - Enh: Added method getYii2Version() to class `components\Yii` to support Yii2 version reading .
 - Chg: Added new extension PhpSpreadsheet (common.extensions.sphpspreadsheet and common.vendors.phpspreadsheet) to replace PHPExcel since it no longer works in PHP 7.2.
 - Chg: Removed extension common.extensions.sexcel
 - Enh: Upgraded `ProductImportManager` to use PhpSpreadsheet for processing Xls / Xlsx file type (for now support these two file types, but more file types can be extended for support)
 - Chg: Change the loading sequence of Sii messages of each common module. It auto merges messages in sequence: 
(1) `shopbay-kernel` common module level messages (2) local module level messages

### Bug fixes:

 - Bug: Loading of `chosen.jquery.js` under `AssetLoaderBehavior` should not use 'minified' filename
 - Bug: Mailer "ReplyTo" email should match the email address of outgoing mail server
 - Bug: Missing loading of required classes causing cart checkout using payment method PayPal Express failed.
 - Bug: Support Codeception test at `shopbay-api`


## Version 0.24 - Jun 24, 2017

This is the initial release of `shopbay-kernel`, part of Shopbay.org open source project. 

It includes code re-architecture and refactoring to separate the `common` component out from old code.
All existing functions and features remain same as inherited from previous code base (v0.23.2).

For full copyright and license information, please view the [LICENSE](LICENSE.md) file that was distributed with this source code.


## Version 0.23 and before - June 2013 to March 2017

Started since June 2013 as private development, the beta version (v0.1) was released at September 2015. 

Shopbay.org open source project was created by forking from beta release v0.23.2 (f4f4b25). 
