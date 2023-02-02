=== WordPress Brute Force Protection - Stop Brute Force Attacks ===
Contributors: guardgiant
Tags: Brute Force, Brute Force Protection, login security, login protection, limit login, stop brute force attacks, brute force login protection
Requires at least: 5.3
Tested up to: 6.1
Stable tag: 2.2.5
Requires PHP: 5.4
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

The only plugin with 100% brute force protection that doesn't lock out genuine users. 

== Description ==

The only plugin with 100% brute force protection that doesn't lock out genuine users.

= Brute Force Protection =

This security plugin implements an approach used by large websites such as Facebook, Google etc. 

When a genuine user makes a successful login to their account using their mobile phone, tablet, or computer GuardGiant starts treating their device as <mark>Trusted</mark>.

* Failed login attempts from trusted devices are directed towards 'Lost Password' forms rather than being subject to account lockouts or additional counter measures.
* Users receive an alert when anyone logs into their account from an unrecognized device or browser.


= Stop Hackers =

GuardGiant uses a range of strong counter-measures to limit login attempts from unrecognized devices. The default behaviour is:

* After 3 failed login attempts from the same unrecognized device, a Google ReCaptcha field is added to the login page. ReCaptcha is a strong counter-measure that is very hard for an automated process to solve.
* After 10 failed login attempts a temporary block of 2 minutes is applied to the device/IP address. No login attempts can be made during this time.
* Each further failed login attempt increases the block time by another minute. This slows down attacks to the point where they quickly become unviable.

All behavior is fully customizable to achieve the level of brute force protection that you require.

= Login History =

A fully featured security log gives you visibility to login attempts on your site. 

* Provides geographic location, device type, IP address and more for each login attempt.
* Filter login attempts by Trusted or Unrecognized devices.
* Search by IP address or username.
* Filter by successful or failed attempts.
* Easy to display successful logins from unrecognized devices that could indicate a hacked account.

This login history  log should form an essential part of your brute force login protection plan. GDPR compliant.

= Other Login Security Improvements =

This security plugin implements various improvements recommended by the Open Web Application Security Project® (OWASP) to keep your site safe:

* Obfuscates login errors to stop hackers detecting valid account names.
* Option to disable XMLRPC.
* And much, much more.

This security plugin is exceptionally easy to use no matter what your level of technical expertise. 

The default settings are highly optimized, designed to prevent brute force attacks whilst not disturbing genuine users from logging in. Advanced users can fully customize the behavior of this plugin to suit their own environment.

= Login Security Plugin - Background Information =

The most common threat that WordPress site owners face is a password guessing attack known as a brute force attack. 
A brute force attack is where an attacker uses a brute force tool (or script) to discover your password by systematically trying every possible combination of letters, numbers, and symbols until the correct password is found. A brute force attack will always work eventually, but the problem for the brute force attacker is that it may take many years to do it.

Brute force prevention techniques focus on slowing down these attacks to the point where they become unviable. 

Using long and complex passwords (that are not dictionary words) is a good brute force attack prevention method to start with. This greatly increases the time an attacker will need.

A common way to stop brute force attacks is to lock out the WordPress account after a defined number of failed authorization attempts (there are various brute force plugins that do this).
The problem with this approach is that the site administrator ends up with unhappy users who have been locked out, often needing manual intervention to regain access. This is not sustainable or desirable for sites of any size. 

The modern approach to brute force prevention is to track the devices that genuine users use to log in, ensuring they are always treated kindly if they forget their password. Unrecognized devices face a progressive but temporary timed lockout.

= Stop Brute Force Attacks =

Periodic monitoring of your security audit log can help you stop brute force attacks. 

Here are patterns that indicate a brute force attack or some other account abuse:

* Failed login attempts using alphabetically sequential usernames or passwords
* Multiple different usernames being used by the same IP address
* Logins for a single account coming from many different IP addresses
* Failed logins at a specific period e.g. every 5 minutes 

== Installation ==

How to install the GuardGiant brute force protection plugin

**Using the WordPress dashboard**

* Navigate to 'Plugins' in the WordPress dashboard.
* Click on 'Add new' to add a new plugin.
* Search for ‘guardgiant brute force’
* Click ‘Install Now’
* Click the 'Activate' link when your download has completed.


**Uploading files via the WordPress Dashboard**

First, download the GuardGiant brute force prevention plugin file to your computer.

* Navigate to 'Plugins' in the WordPress dashboard.
* Click on 'Add new' to add a new plugin.
* Click on 'Upload Plugin' at the top of the page.
* Click 'Choose File'
* Select guardgiant.zip from your computer
* Click ‘Install Now’
* Click the 'Activate' link when your download has completed.


== Frequently Asked Questions ==

= What level of expertise do I need to configure this plugin? =

This plugin is exceptionally easy to use no matter what your level of technical experience. The default settings are highly optimized to limit login attempts and prevent brute force attacks, whilst not disturbing genuine users from logging on.

For advanced users, you can fully customize the behavior of the plugin to ensure it works best for your application.

= Is this plugin compatible with Cloudflare or other CDNs? =

Yes.

Load balancers and CDNs are known as reverse proxies. Due to the nature of these services, all visits to your website are logged with the IP address of the proxy rather than the visitor’s actual IP address. To remedy this, the visitor's IP address is provided in a 'header field' which GuardGiant will pick up and use.

= Why am I getting attacked? =

Hackers want to gain access to your website for various reasons:

* Place backlinks from your site to improve their site’s Pagerank.
* Redirect your visitors to malicious sites or use your website to distribute spam and viruses.
* Use your server as part of a botnet to launch DDOS attacks.
* Use the same hacked account/password on other sites.
* Collect personal information that they may be able to sell on other sites.
* Ransomware attempt.

It is not uncommon for popular WordPress websites to receive hundreds or even thousands of attacks every day.
These attacks can seriousy damage your reputation if they are allowed to succeed. 
By using the guardgiant security plugin, these bruteforce attacks will be met with extremely strong counter-measures 
to ensure the security of your WordPress website.

= How can I view login activity on my website =

The security audit log provides relevant information from the past 30 days. 
You can filter security log entries by:

1. Time period for all log entries you wish to see.

2. Whether the users device was trusted or not.

3. The type of security event you wish to see:

* Successful logins to your website.
* Failed logins to your website.
* All authorization attempts.

4. Search log entries by IP address.

5. Search log entries by username.

The time and date of each security event is shown along with username, IP address, IP location, Device type and outcome/message.

= How do I access the security log =

* First go to your WordPress Dashboard. Choose "guardgiant" in the lefthand menu.
* Select the activity Log.

= How to change the settings for brute force protection =

Login to the WordPress dashboard and select GuardGiant from the left hand menu.
The **Prevent Brute Force Attacks** tab is shown. You can choose to:

* Lockout user accounts for a specified time period.
* Option to never lockout accounts when a user is using a trusted device.
* Option to send users a security alert when there is a successful login from an unrecognized device.

For bruteforce attacks from specific IP addresses you can:

* Display a captcha after a specified number of failed login attempts.
* Block the IP address after a certain number of failed attempts (for a specified number of minutes).
* Option for a progressive block where the time increases with each failed attempt.

= Should I disable XMLRPC? =

The XML-RPC (XML Remote Procedure Call) functionality in WordPress has become a backdoor for hackers trying to exploit a WordPress installation.
It allows your site to be updated with a single command triggered remotely.

It is recommended to disable this feature to reduce the attack surface of your site.

= Should I enable google reCaptcha =

Google reCaptcha provides a barrier that helps to stop brute force attacks. However, the user experience is somewhat worsened by asking to select traffic lights every time they log in. 

The beauty of this security plugin is that google reCaptcha is only to deployed to specific IP addresses and only after a user defined number of failed login attempts.
This is another way that GuardGiant implements brute force protection without disturbing genuine users. 

= Can I 'whitelist' certain IP addresses =

Yes, GuardGiant supports IP address whitelists. For instance, you can whitelist the IP address of your office to ensure you always have access. 

Caution is urged when using whitelists as they can provide an attack vector for brute force hackers. 

= Can I 'whitelist' certain users =

Yes, you can whitelist individual users so that their accounts are never locked out. 

Caution is urged when using whitelists as they can provide an attack vector for brute force hackers. 

= Why should I obfuscate error messages =

By default, WordPress login error messages indicate whether a valid username has been entered. 
Attackers can use this functionality to harvest a list of usernames that are then used as part of a brute force attack. 
Modern security implementations now recommend a generic 'incorrect username/password'. 

This security plugin implements obfuscation of login errors.

= Why do I need to limit login attempts =

By default, WordPress does not track or limit the number of failed login attempts that can be made. 
Without a security plugin or means to prevent brute force attacks, an attacker can try many thousands of login attempts in rapid succession to try to gain access. 
It is important to note that the legacy approach of simply locking out users after a number of failed attempts is a poor solution that causes user frustration and can easily be exploited in a DDOS attack. 

This security plugin implements modern best practice by tracking the devices that users log on with to ensure real users are never impacted.


== Screenshots ==

1. Recent login activity in the security log showing **brute force login protection**.
2. Login screen when reCaptcha has been deployed. WordPress **login brute force protection**.
3. New device sign in email. **Brute force attack prevention** by notifying the user that their account could be compromised.
4. Main guard giant settings page. A **WordPress security plugin**.

== Changelog ==


= 2.2.5 =
* Added the ability to set how long records are retained in the acitivty log.
* Minor bug fixes.

= 2.2.4 =
* Minor bug fixes.

= 2.2.3 =
* Refuse guest access to certain API calls (stops user enumeration).
* Obfuscate error messages related to password resets (stops user enumeration).
* Other security enhancements.

= 2.2.2 =
* Performance improvements.

= 2.2.1 =
* Added help tabs to settings pages.

= 2.2.0 =
* Added the ability to disable XMLRPC to reduce attack vectors.
* Performance improvements.

= 2.1.1 =
* Improvements to the IP address geolocation feature.

= 2.1.0 =
* Initial release to WordPress.



