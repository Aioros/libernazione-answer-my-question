=== Plugin Name ===
Contributors: Matt Kaye
Donate link: http://netcandy.co
Tags: comments, Question and Answer
Requires at least: 3.0
Tested up to: 3.4.2
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Allow users to ask the site admin a question. The flow of conversation could either be public on the website, or private via direct email.

== Description ==
Answer My Question is a simple plugin that allows blog users to send questions to the admin.

== Installation ==
Upload the answer-my-question directory to your WordPress plugin directory, activate it, and you're done.

== Directions ==
In order to create a link to the modal window, wrap any HTML page element with the short tag [amq_modal].
EX: [amq_modal] Click on me, I become a modal window! [/amq_modal]

To show a list of questions and answers, publish either a page or post with the shortcode [list_amq]. Only answered questions that are set to appear on the site will be shown.

Both the modal window and the full list of question and answers can be styled. You will need to edit these two files:
answer_my_question_full_list.css
answer_my_question_site_modal.css

They are found in the css directory of the plugin folder. IMPORTANT: You will need to back up your css changes if you update the plugin version. Upgrading will overwrite your custom changes to the css.

== Screenshots ==
1. Question administration table for the admin
2. Answer the users question either on the website or directly via email
3. Modal window on client facing side of the site for the user to fill out

== Changelog ==

= 1.1 =
* Plugin is now shown as a top level navgiation item in the administration panel
* Modal window can be fully styled
* Spawning of the modal is now done using a short code
* QA list can be fully styled
* Specific questions can be selected to not appear on site in the full list
* Plugin localization added
* Plugin path fixed when using SSL connection

= 1.2 =
* Fixed XSS vulnerability


= 1.3 =
* New SQL table structure was not being applied on plugin update
