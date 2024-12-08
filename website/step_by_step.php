<?php
require('../includes/application_top.php');
require(DIR_WS_INCLUDES.'account_common_top.php');
$page_header = 'How does it Work?';
$page_title = 'Step by step guide. ';
$show_top_images = false;
require(DIR_WS_INCLUDES.'header.php');
$s = '
<h2><span style="font-weight:bold; font-style:normal;">Step 1</span> Account Registration</h2>
<p>Click on the <a href="/signup.php">Signup</a> from the top menu, fill in your email, name, password, and click on the <b>Sign Up</b> button.</p>
<h2><span style="font-weight:bold; font-style:normal;">Step 2</span> Buying '.ucfirst(WORD_MEANING_SHARE).'s</h2>
<p>When you are purchasing '.WORD_MEANING_SHARE.'s of a '.DAO_NAME.' you are receiving rights to gain '.WORD_MEANING_DIVIDEND.'s from this '.DAO_NAME.' and at any time you can sell your '.WORD_MEANING_SHARE.'s for the best price.
Each '.WORD_MEANING_SHARE.' has its own '.WORD_MEANING_DIVIDEND.' and it is paid on the daily, weekly, or monthly basis. Your income includes '.WORD_MEANING_DIVIDEND.'s and growing price of '.WORD_MEANING_SHARE.'s. 
To purchase a '.WORD_MEANING_SHARE.' click on the <b><a href="/exch_shares.php">All '.ucfirst(DAO_NAME).'s</a></b> under the <b>'.ucfirst(DAO_NAME).'s</b> menu, select a '.WORD_MEANING_SHARE.' that you are willing to buy and click the <b>Buy</b> button.
You can make payment via {$payment_providers_with_urls}.
Immediately after your payment your balance will be increased, and during short time the purchased '.WORD_MEANING_SHARE.'/'.WORD_MEANING_SHARE.'s will be transferred to your ownership.</p>
<h2><span style="font-weight:bold; font-style:normal;">Step 3</span> Selling '.ucfirst(WORD_MEANING_SHARE).'s</h2>
<p>To sell your '.WORD_MEANING_SHARE.'s click on the <b><a href="/acc_exch_my_shares.php">My '.ucfirst(WORD_MEANING_SHARE).'s</a></b> under the <b>'.ucfirst(DAO_NAME).'s</b> menu, select a '.DAO_NAME.' and click the <b>Sell</b> button. Then specify the amount you want to receive for each '.WORD_MEANING_SHARE.', and enter the number of '.WORD_MEANING_SHARE.'s that you want to sell.
If you want to sell your '.WORD_MEANING_SHARE.'s quick you have to specify the less price. The '.WORD_MEANING_SHARE.'s which have less price will be sold first.
Also the time where your '.WORD_MEANING_SHARE.'s on sale before they are actualy sold depends on the activity on market. If market is slow and there are no users willing to buy your '.WORD_MEANING_SHARE.'s you will wait longer.</p>
<h2><span style="font-weight:bold; font-style:normal;">Step 4</span> Cash Out</h2>
<p>As soon as you have minimum withdrawal amount on your balance you can make payout to your <b>{$payout_providers_with_urls}</b> account.
Click on the <b><a href="/acc_withdraw.php">Cash-Out</a></b> from the top menu, enter amount to withdraw, and click on the <b>Withdraw</b> button.
Usually the withdrawal takes 2 - 3 business days.
</p>
';
$s = str_replace("\r", '</p><p style="text-align:justify; padding-right:20px;">', $s);
$s = $user_account->parse_common_params($s, true, true);
echo $s;
require(DIR_WS_INCLUDES.'footer.php');
?>
</body>
</html>
