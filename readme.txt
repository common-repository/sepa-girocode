=== SEPA Girocode ===
Contributors: halli77
Tags: Girocode, EPC, SEPA, SCT, Überweisung, Mobile Payments, QR-Code, shortcode, generator, bezahlcode, rechnung, zahlschein
Requires at least: 4.0
Tested up to: 4.6.0
Stable tag: 0.5.1

Create EPC-Codes (in Germany known as Girocode) for money transfer | Girocode-Barcode für SEPA-Überweisungen erstellen

== Description ==
With Girocode/EPC-Codes you can easily provide payments information in a Quick-Response-Code. Customers just have to scan the code with a supported banking app.

== Installation ==
1. Upload the plugin files to the `/wp-content/plugins/sepa-girocode` directory, or install the plugin through the WordPress plugins screen directly.
1. Activate the plugin through the 'Plugins' screen in WordPress.
1. Here you are!

== Frequently Asked Questions ==

= How do I create a Girocode? =

Just use the shortcode [girocode] in any page or post you want to display a girocode.

= And how to show the girocode generator form? =

Just use the shortcode [girocode-generator].

= Which parameters are available for [girocode]? =

* iban: International Bank Account Number of the beneficiary
* bic: Bank Identifier Code
* beneficiary: Name of the beneficiary
* amount: Amount of money to transfer in EUR
* purpose: Description of the payments purpose
* purposecode: Standard-Codes for payment purposes, e.g. CHAR for Charity-Payments
* reference: Alternative to unstructured purpose description, use of reference overrules purpose-field
* isclickable: With this field set to 1 (standard) the QR-Code can be downloaded als .girocode file
* frame: With this field set to 0 (standard = 1), the girocode-frame will be supressed
* dimension: Width (and also height) of the code in pixels (standard = 150)
* divclass: Optional CSS-class for wrapping div-element

= Which parameters are available for [girocode-generator]? =

* demo: with demo="1" the form will be populated with example values, containing a donation for the German Red Coss

= Come on, show me an example ... =

OK, let's donate 5 EUR for the German Red Cross:
[girocode iban="DE63370205000005023307" beneficiary="German Red Cross" amount="5" purpose="Donation"]

Display generator form in demo mode:
[girocode-generator demo="1"]

= Which apps do support Girocode? =
Have a look at www.girocode.de for a list of supported banking apps.

= Which tool do you use to create the QR-Code? =
http://phpqrcode.sourceforge.net/

== Screenshots ==
1. Some example Girocodes

2. Girocode generator form

== Changelog ==
= 0.5.1 (2016-09-09) =
* Added girocode-generator demo mode

= 0.5 (2016-09-04) =
* Added girocode-generator form

= 0.4.4 (2016-08-27) =
* Fixed transient lifetime

= 0.4.3 (2016-08-24) =
* Fixed internal SVN issue

= 0.4 (2016-08-24) =
* Added girocode-frame
* Added parameter (frame, dimension, divclass)
* Code refactor

= 0.3 =
* Added new parameters (clickable, purposecode, reference)
* QR-Codes now can be clickable for supported apps
* Fixed broken div-element

= 0.2 =
* Changed QR-Code-Enginge from google api to local lib (http://phpqrcode.sourceforge.net/)

= 0.1 =
* Initial release.