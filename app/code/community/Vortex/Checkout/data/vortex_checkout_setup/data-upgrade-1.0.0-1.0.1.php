<?php
/** @var Mage_Catalog_Model_Resource_Setup $this */
$installer = $this;
$installer->startSetup();

$staticBlocks = array(
    array(
        'id'        => 'checkout_terms_and_conditions',
        'title'     => 'Checkout - Terms and conditions',
        'content'   =>
            '<div id="website" class="tabbed-content-container open-content">
                <h3 id="website-use" class="sub-nav-current">
                    Use of the Website
                </h3>
                <p>Welcome to <a href="http://www.peacocks.co.uk/">http://www.peacocks.co.uk/</a> website terms and conditions. These terms and conditions apply to the use of <a href="http://www.peacocks.co.uk/">http://www.peacocks.co.uk/</a> and the selling of our products to you subject to the conditions set out on this page.</p>
                <p>By using <a href="http://www.peacocks.co.uk/">http://www.peacocks.co.uk/</a> you agree to be bound by the terms and conditions set out below. If you do not agree to be bound by these terms and conditions, you may not use or access this website.</p>
                <p>If you have any questions relating to these terms and conditions, please contact our customer services team before you place an order at <a href="mailto:online-customer-services@peacocks.co.uk">online-customer-services@peacocks.co.uk</a>. This site is owned and operated by Peacocks Stores Ltd ("Peacocks", "we" or "us").</p>
                <p>Trading address: Capital Link, Windsor Road, Cardiff, South Glamorgan CF24 5NG</p>
                <p>Registered office: Waverley Mills, Langholm, Dumfriesshire, DG13 OEB</p>
                <p>Registered number: SC285031</p>
                <p>VAT number: GB 263653059</p>
                            <h3 id="ipr">
                                Intellectual Property Rights
                            </h3>
                <p>All intellectual property rights, trademarks and copyright for all material on the website is owned by or licensed to us. You may use any of the information within <a href="http://www.peacocks.co.uk/">http://www.peacocks.co.uk/</a> for your personal use only. You agree not to copy, transmit, reproduce, publish or distribute any material from the website or use any information for non-personal or commercial purposes. You may print extracts from the website or download and copy information to a local hard drive for your personal use only. You may not link or frame the website or any part of it without our express permission.</p>
                            <h3 id="your-account">
                                Your Account
                            </h3>
                <p>You are responsible for the confidentiality and maintenance of your personal account information and password. You accept responsibility for all activities that occur under your account and password. It is important that you keep your personal details and password confidential and secure. If you have any reason to suspect that your password has become known by someone else, or has been or is likely to be used without your authorisation, you should contact us immediately. We shall not be liable to any person for any loss or damage as a failure by you to protect your password or account details.</p>
                <p><a href="http://www.peacocks.co.uk/">http://www.peacocks.co.uk/</a> reserves the right to refuse access to the website, terminate personal accounts, amend and remove content and cancel orders (at no cost to you).</p>
                            <h3 id="registration">
                                Registration
                            </h3>
                <p>You guarantee that when you register with us as a customer, all of the information that you provide is true, accurate and up to date. If any of the information provided changes, you must inform us immediately by contacting our customer services team at <a href="mailto:online-customer-services@peacocks.co.uk">online-customer-services@peacocks.co.uk</a>.</p>
                <p>You must not impersonate any other person or entity or use a false name or a name that you do not have authority to use. You must not use the website in any way which will cause, or is likely to cause, the website to be impaired, damaged or interrupted in any way. By placing an order on our site you warrant that you are legally entitled to enter into binding contracts.</p>
                            <h3 id="your-conduct">
                                Your Conduct
                            </h3>
                <p>You must not use <a href="http://www.peacocks.co.uk/">http://www.peacocks.co.uk/</a> for any of the following activities:</p>
                <ul>
                <li>For any fraudulent activity</li>
                <li>In connection with any criminal activity or unlawful act</li>
                <li>To transmit or distribute a virus, Trojan, worm or logic bomb</li>
                <li>To transmit any material which is illegal, malicious, in any way offensive, obscene, defamatory or menacing</li>
                <li>To cause annoyance, inconvenience or needless anxiety to others</li>
                <li>In breach of copyright, trademark, confidence, privacy or other right</li>
                <li>Send unsolicited advertising or promotional material, often referred to as spam. Under the Computer Misuse Act 1990, breaching this provision may be a criminal offence and we will report any breach to the law enforcement authorities and disclose your identity to them.</li>
                </ul>
                            <h3 id="website-access">
                                Access to http://www.peacocks.co.uk/
                            </h3>
                <p>We will make every effort to ensure that <a href="http://www.peacocks.co.uk/">http://www.peacocks.co.uk/</a> is available to use and is operating properly without any errors. However, we reserve the right (from time to time) to restrict access to the website to allow essential maintenance or upload new services and/or improvements. We will ensure that any interruptions are kept to a minimum.</p>
                            <h3 id="damage">
                                Damage to Your Computer
                            </h3>
                <p>We have made every effort to ensure that <a href="http://www.peacocks.co.uk/">http://www.peacocks.co.uk/</a> is free from viruses and defects. However, it is your responsibility to ensure that your computer is properly protected and updated with specialist screening software available to screen out anything that may damage it. We cannot guarantee that using this website or other websites which can be accessed through <a href="http://www.peacocks.co.uk/">http://www.peacocks.co.uk/</a> will not result in any damage to your computer.</p>
                            <h3 id="links">
                                Links to Other Websites
                            </h3>
                <p>There may be links on <a href="http://www.peacocks.co.uk/">http://www.peacocks.co.uk/</a> that will take you to other websites. We do not monitor these other websites and cannot control their content, privacy policies or services. As such we do not accept any liability from the use of these websites.</p>
		    </div>'
    )
);


$storeId = Mage::app()->getStore('default')->getId();

foreach ($staticBlocks as $staticBlock) {
    $block = Mage::getModel('cms/block')->getCollection()
        ->addFieldToFilter('identifier', $staticBlock['id'])
        ->getFirstItem();
    if(!$block || !$block->getBlockId()) {
        $staticBlockArray = array(
            'title'         => $staticBlock['title'],
            'identifier'    => $staticBlock['id'],
            'is_active'     => 1,
            'stores'        => array($storeId),
            'content'       => $staticBlock['content']
        );
        Mage::getModel('cms/block')->setData($staticBlockArray)->save();
    }
}


$installer->endSetup();