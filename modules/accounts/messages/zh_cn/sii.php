<?php
/**
 * Module Message translations (this file must be saved in UTF-8 encoding).
 * It merges also messages from:
 * [1] kernel common messages
 */
$commonMessages = require(Yii::app()->basePath.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.KERNEL_NAME.'/messages/zh_cn/sii.php');
return array_merge($commonMessages,[
    //put local translation here e.g. 'translate'=>'翻译',
    'account' => '账号设置',
    'Address'=>'地址',
    'A new activation token key is sent to your new mailbox <em>{email}</em>' => '新激活码已发送到您的电子邮箱<em>{email}</em>',
    'A new password is sent to your email <em>{email}</em>' => '新密码已发送到您的电子邮箱<em>{email}</em>',
    'Account' => '账号',
    'Account Information' => '账号信息',
    'Account Activation' => '账号激活',
    'Account Name' => '用户名',
    'Activation Key Regenerated' => '系统已重新产生激活码',
    'Already have an account? Sign in here' => '如已拥有账号请从这里登入',
    'Ask Question' => '提问问题',
    'Best Selling Products'=>'最热卖产品',
    'Growth' => '业务成长',
    'Bad Request' => '错误',
    'Caution'=>'注意事项',
    'Change Email' => '更改电子邮箱地址',
    'Change Password' => '更改密码',
    'Change Avatar'=>'更改Avatar',
    'Changing email address is effectively changing your login id, and after changing you current email address will be invalid for login.<br>'=>'更改电子邮箱地址等同于更改登入用户名。电子邮箱地址更改后您将无法用现有的电子邮箱登入。<br>',
    'Check your email and follow the link to complete the process of creating your account.' => '请查看您的电邮然后根据里面的步骤完成创建您的账号。',
    'Click to Refresh' => '按这里刷新',
    'Confirm' => '确认',
    'Confirm New Email' => '确认新电子邮箱地址',
    'Confirm New Email must be same as New Email' => '确认新电子邮箱地址必须与新电子邮箱地址相等',
    'Confirm New Password' => '确认新密码',
    'Confirm New Password must be the same as New Password' => '确认新密码必须与新密码相等',
    'Confirm Password' => '确认密码',
    'Confirm password must be same as Password' => '确认密码必须与密码相等',
    'Current Password' => '现有密码',
    'email' => '电子邮箱',
    'Email' => '电子邮箱',
    'Email address not found. Please try again.' => '电子邮箱地址无法找到。请再试一次。',
    'Email is changed successfully. You are required to re-activate your account at your next login.' => '更改电子邮箱地址成功。请注意您必须在下一次登入时重新激活账号。',
    'Female' => '女',
    'Fields with <span class="required">*</span> are required.' => '所有 <span class="required">*</span> 的栏项必须填写。',
    'Log in' => '登入',
    'Forgot Password' => '忘记密码',
    'Items' => '商品',
    'I have read and accepted Terms of Service' => '我同意并接受服务条款',
    'If you cannot see the image clearly, click on the image to get a new one.' => '如果图片不清楚，请点击图片要求一个新的图片。',
    'Incorrect Current Password' => '错误现有密码',
    'Invalid Password' => '密码错误',
    'Total Items' => '商品数量',
    'Last update time' => '上一次更新日期',
    'Last update time {datetime}' => '上一次更新日期{datetime}',
    'Latest News' => '最新新闻',
    'Male' => '男',
    'Manage Account' => '账号管理',
    'Member since' => '账号创建自',
    'Merchant Account'=>'商家账号',
    'New Email' => '新电子邮箱地址',
    'New Email cannot be the same as current email {email}' => '确认新电子邮箱必须与现有电子邮箱相等',
    'New Password' => '新密码',
    'New Password cannot be the same as Current Password' => '新密码必须与现有密码相等',
    'Total Orders' => '订单数量',
    'Orders' => '订单',
    'password' => '密码',
    'Password' => '密码',
    'Password changed successfully.' => '密码更改成功。',
    'Password is reset successfully.' => '密码重新设置成功。',
    'Please activate your account using the token key that we have sent to your mailbox <em>{email}</em>.' => '我们已发送账号激活码到您的电子邮箱：<em>{email}</em>。',
    'Please activate your account using this new token key that we have sent to your mailbox <em>{email}</em>.' => '',
    'Please enter the email address you have registered with us.' => '请输入您所注册的电子邮箱。',
    'Please enter the letters (case-insensitive) as they are shown in the image above.' => '请输入图片中的字母（大小写均可）。',
    'Please enter the letters (case-insensitive) at shown at left. Click on the image to get a new one.' => '请输入左边图片中的字母（大小写均可）。点击图片可以要求新的图片。',
    'Previous activation keys that you had received are no longer valid.' => '您之前收到的激活码已无效。',
    'profile' => '个人资料',
    'Profile' => '个人资料',
    'Recent Activity' => '最近活动',
    'Resend Activation Email' => '系统重新发送账号激活电邮',
    'Revenue ($)' => '营业额（¥）',
    'Sales Amount' => '销售额',
    'Save' => '储存',
    'Select Country' => '选择国家',
    'Select Gender' => '选择性别',
    'Select Langague' => '选择语言',
    'Show All' => '显示全部',
    'Show All POs' => '显示全部订单',
    'Show All SOs' => '显示全部运单',
    'Sign up' => '注册账号',
    'Submit' => '提交',
    'Tasks' => '工作操作',
    'Thanks for signing up {service}' => '谢谢您注册{service}',
    'Thanks for signing up {app}!'=>'感谢您注册{app}！',
    'The requested page does not exist' => '您所查找的网页不存在',
    'The requested page does not exist.' => '您所查找的网页不存在。',
    'This will not affect your current session, but you are required to use new password for future logins.' => '此密码更改不影响当前使用，但请注意您必须在下一次登入时使用新密码。',
    'This name will be used as your display name when you post comments, ask questions etc , and the default recipient name when you checkout.'=>'当您发表评论或提出问题时此名字将用做您的身份显示，此外也用做购物车结账的默认收件人。',
    'Unauthorized Access' => '您无权限使用',
    'Unauthorized role access' => '您无权限使用',
    'Undefined login state. Please try again.' => '登录状态无法定义。请再试一次。',
    'Username' => '用户名称',
    'Verification Code' => '验证码',
    'Verifying your email address and password' => '我们需要核对您的电子邮箱地址与密码',
    'View Profile' => '个人资料',
    'Welcome! Your account is activated successfully.' => '恭喜欢迎！您的账号已激活成功。',
    'Welcome! 欢迎! Selamat Datang!' => 'Welcome! 欢迎! Selamat Datang!',
    'Welcome to {app}!'=>'欢迎您来到{app}！',
    'Yes'=>'是',
    'You are required re-activate your account using new email address to login.' => '请注意您必须在下一次登入时使用新的电子邮箱并重新激活账号。',
    'You have not accepted the User Agreement' => '您还没有接受用户使用与服务条款',
    'Your account is already activated.' => '您的账号已激活成功。',
    'ask' => '提问',
    'Hit'=>'点击率',
    'Pageview'=>'浏览量',
    'Visitors'=>'访客',
    'Visits'=>'店铺浏览统计',    
    'Totals'=>'总计',
    '{lastPeriod} Total: {lastTotal}' => '{lastPeriod}总计: {lastTotal}',
    '{growth} {growthPeriod}, {lastPeriod} Total: {lastTotal}' => '{growthPeriod}{growth}, {lastPeriod}总计: {lastTotal}',
    'YoY' => '同比去年增长',
    'MoM' => '同比上月增长',
    'WoW' => '同比上周增长',
    'DoD' => '同比昨日增长',
    'Customers'=>'客户',
    'Total Customers'=>'客户总数',
    'Top Customers'=>'最多消费的客户',
    'Total Spent' => '总消费',
    'Trends'=>'趋势',
    'Total Amount'=>'总购买额',
    'Conversions'=>'转换率',
    'Added to Cart'=>'加入购物车',
    'Checkout'=>'购物车结算',
    'Purchased'=>'确定购买',    
    'Revenue Left in Cart'=>'滞留在购物车的潜在收入',
    'Total Revenue Left'=>'潜在收入总计',    
    'This total does not include store level discount and tax'=>'此总计不计全店折扣和税费',
    'Network'=>'社交网络',
    'Network Logout'=>'网络退出',
    'Network Unlink'=>'网络脱绑',
    'Network Link Error'=>'网络绑定错误',
    'Network Unlink Error'=>'网络脱绑错误',
    'Sign out Network'=>'退出网络',
    'Social Networks'=>'社交网络',
    'Linked Accounts to Social Networks'=>'社交网络绑定账号',
    'Link Network'=>'绑定网络',
    'Unlink Network'=>'脱绑网络',
    'Connected to Network'=>'网络已登入',
    'Not Connected to Network'=>'网络已退出',
    'Failed to link your account to {network}.'=>'您的账号无法与{network}绑定',
    'You can link your social network accounts to {app}, which enables you to be able to login {app} by using social network account. If any of the social networks below is shown with locked icon, it means that you are currently connected to it.'=>'您可以将您的社交网络账号与{app}绑定。这意味着您可使用社交网络账号来登入{app}。若以下任一社交网络有显示关闭的锁头图标，其表示当前您与该网络是连线的。',
    'You have linked {network} to {app} successfully and you will be able to login {app} using {network} account in your future logins.'=>'您已成功将{network}与{app}绑定。您将可使用{network}帐号来登入{app}。',
    'You have unlinked {network} from {app} successfully and you will not be able to login using {network} account. If you wish to use {network} account again to login {app}, you may relink it.'=>'您已成功从{app}脱绑{network}，您将无法继续使用{network}账号登入{app}。如果你想要继续使用{network}账号登入，您可以重新绑定。',
    'You have logged out {network} successfully but this does not affect your current session and use of {app}.'=>'您已成功退出{network}，但这不影响当前您对{app}的使用。',
    'You will be signing out this social network, but not {app}. You can continue to use {app} until you logout {app}.\\n\\nDo you want to proceed?'=>'您即将要退出此网络，但不退出{app}。您仍可继续使用{app}直到您退出{app}。',
    'Linking this social network to {app} enables you to be able to login {app} using its account.\\n\\nDo you want to link this network?'=>'将此网络与{app}绑定允许您使用网络账号登入{app}。您是否确认要绑定此网络？',
    'If you unlink this social network from {app}, you will not be able to login {app} using its account.\\n\\nDo you realy want to unlink this network?'=>'如果您从{app}中脱绑此网络，您将无法再使用网络账号登入{app}。您是否确认要脱绑此网络？',
    'Logged out {app}'=>'您已退出{app}',
    'n<=1#You had logged in {app} earlier on using following network account and you may still connected with it. Logging out {app} does not auto logout network. {networkList}|n>1#You had logged in {app} earlier on using following network accounts and you may still connected with them. Logging out {app} does not auto logout networks. {networkList}'=>'n<=1#您之前使用了下列网络账号登入{app}，并且当前您可能与网络还是连线的。请注意退出{app}并不会自动退出网络。{networkList}|n>1#您之前使用了下列网络账号登入{app}，并且当前您可能与网络还是连线的。请注意退出{app}并不会自动退出网络。{networkList}',
    'Email <em>{email}</em> is already registered.'=>'电邮地址已注册： <em>{email}</em>',
    'Order {order_no} not found.'=>'订单 {order_no} 无法找到。',
    'Please try other email address.'=>'请用其他电邮地址注册。',
    'Other account information are extracted from the shipping address you had previously filled for order {order_no}.'=>'其他账号信息是来自您之前的订单 {order_no} 里的送货地址。',
]);
