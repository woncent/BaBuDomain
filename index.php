<?php
$xurl = $_SERVER['HTTP_HOST'];

// 获取根域名函数
function getRootdomain($domain) {
    $suffix = array("com","com.cn","net","net.cn","org","org.cn","cn","me","mobi","in","la","biz","tv","ltd","vip","wang","beer","ai","cc","top","club","info");
    $domainArr = explode(".", $domain);
    $l = count($domainArr);
    $key = 0;
    
    for($i = 0; $i < $l; $i++) {
        if(in_array($domainArr[$i], $suffix)) {
            $key = $i;
            break;
        }
    }
    
    $inSuffixs = '';
    for($i = $key; $i < $l; $i++) {
        $inSuffixs .= "." . $domainArr[$i];
    }
    
    return $domainArr[$key-1] . $inSuffixs;
}

// 检测语言偏好
function detectLanguage() {
    if(isset($_GET['lang'])) {
        return $_GET['lang'] === 'en' ? 'en' : 'zh';
    }
    
    if(isset($_COOKIE['lang'])) {
        return $_COOKIE['lang'] === 'en' ? 'en' : 'zh';
    }
    
    if(isset($_SERVER['HTTP_ACCEPT_LANGUAGE'])) {
        $lang = substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
        return $lang === 'zh' ? 'zh' : 'en';
    }
    
    return 'zh';
}

// 从list.html读取价格数据
function getDomainPrice($domain, $lang) {
    $filePath = __DIR__ . '/list.html';
    if (!file_exists($filePath)) return null;
    
    $content = file_get_contents($filePath);
    preg_match_all('/<tr[^>]*>\\s*<td[^>]*>([^<]+)<\\/td>\\s*<td[^>]*>[^<]+<\\/td>\\s*<td[^>]*>([^<]+)<\\/td>\\s*<td[^>]*>([^<]+)<\\/td>\\s*<\\/tr>/is', $content, $matches, PREG_SET_ORDER);
    
    foreach ($matches as $row) {
        $domainName = strtolower(trim($row[1]));
        if ($domainName === $domain) {
            $price = $lang === 'zh' ? trim($row[2]) : trim($row[3]);
            return $lang === 'zh' ? '¥' . $price : '$' . $price;
        }
    }
    
    return null;
}

// 获取4.cn和22.cn的优惠价格
function getDiscountPrice($domain, $lang) {
    $filePath = __DIR__ . '/list.html';
    if (!file_exists($filePath)) return null;
    
    $content = file_get_contents($filePath);
    preg_match_all('/<tr[^>]*>\\s*<td[^>]*>([^<]+)<\\/td>\\s*<td[^>]*>[^<]+<\\/td>\\s*<td[^>]*>([^<]+)<\\/td>\\s*<td[^>]*>([^<]+)<\\/td>\\s*<\\/tr>/is', $content, $matches, PREG_SET_ORDER);
    
    foreach ($matches as $row) {
        $domainName = strtolower(trim($row[1]));
        if ($domainName === $domain) {
            $price = $lang === 'zh' ? floatval(trim($row[2])) : floatval(trim($row[3]));
            $discountedPrice = intval($price * 0.92); // 计算8%的优惠，取整
            return $lang === 'zh' ? '¥' . $discountedPrice : '$' . $discountedPrice;
        }
    }
    
    return null;
}

$domain = strtolower(getRootdomain($xurl));
$lang = detectLanguage();
$domainPrice = getDomainPrice($domain, $lang);
$usdPrice = str_replace('$', '', getDomainPrice($domain, 'en')); // 始终获取美元价格
$cnPrice = str_replace('¥', '', getDomainPrice($domain, 'zh')); // 获取中文价格
$discountPrice = getDiscountPrice($domain, $lang); // 获取4.cn和22.cn的优惠价格

$i18n = array(
    'zh' => array(
        'nav' => array('home'=>'首页','whois'=>'WHOIS','contact'=>'联系我们','lang'=>'English'),
        'header' => array('title'=>'优质域名出售','subtitle'=>'Domain for Sale!','btn1'=>'立即购买'),
        'purchase' => array(
            'options' => array(
                array('name'=>'阿里云', 'desc'=>'国内领先的云计算及域名服务提供商', 'icon'=>'fa-cloud', 'color'=>'blue', 'url'=>'https://mi.aliyun.com/detail/online.html?domainName=', 'price' => '¥'.$cnPrice, 'recommended' => true),
                array('name'=>'4.cn', 'desc'=>'国内专业域名交易平台', 'icon'=>'fa-shopping-bag', 'color'=>'green', 'url'=>'https://www.4.cn/search/detail/domain/?lang=zh&domain=', 'price' => $discountPrice, 'discount' => '8%'),
                array('name'=>'22.cn', 'desc'=>'爱名网-国内知名域名交易平台', 'icon'=>'fa-shopping-cart', 'color'=>'red', 'url'=>'https://am.22.cn/member/buyer/adddelegatev.aspx?&domain=', 'price' => $discountPrice, 'discount' => '8%'),
                array('name'=>'Atom', 'desc'=>'专业域名投资交易平台', 'icon'=>'fa-rocket', 'color'=>'purple', 'price' => '$'.$usdPrice, 'url'=>'https://www.atom.com/name/'),
                array('name'=>'GoDaddy', 'desc'=>'全球知名域名交易平台', 'icon'=>'fa-globe', 'color'=>'orange', 'price' => '$'.$usdPrice, 'url'=>'https://www.godaddy.com/forsale/'),
                array('name'=>'Escrow', 'desc'=>'安全可靠的第三方托管交易平台', 'icon'=>'fa-lock', 'color'=>'teal', 'price' => '$'.$usdPrice, 'form' => '<form action="https://www.escrow.com/checkout" method="post" target="_blank"><input type="hidden" name="type" value="domain_name"><input type="hidden" name="non_initiator_email" value="8812182@qq.com"><input type="hidden" name="non_initiator_id" value="2374248"><input type="hidden" name="non_initiator_role" value="seller"><input type="hidden" name="title" value="B'.$domain.'"><input type="hidden" name="currency" value="USD"><input type="hidden" name="domain" value="'.$domain.'"><input type="hidden" name="price" value="'.$usdPrice.'"><input type="hidden" name="concierge" value="false"><input type="hidden" name="with_content" value="false"><input type="hidden" name="inspection_period" value="1"><input type="hidden" name="fee_payer" value="seller"><input type="hidden" name="return_url" value=""><input type="hidden" name="button_types" value="buy_now"><input type="hidden" name="auto_accept" value=""><input type="hidden" name="auto_reject" value=""><input type="hidden" name="item_key" value="undefined"><button class="w-full price-tag" type="submit">立即购买 ($'.$usdPrice.')</button><img src="https://t.escrow.com/1px.gif?name=bin&price='.$usdPrice.'&title='.$domain.'&user_id=2374248" style="display: none;"></form>'),
            )
        ),
        'contact' => array('title'=>'域名经纪人','name'=>'BaBu','position'=>'专业域名经纪人','qq'=>'QQ: 8812182','wechat'=>'微信: 8812182','email'=>'邮箱: 8812182@qq.com'),
        'footer' => array('copyright'=>'版权所有')
    ),
    'en' => array(
        'nav' => array('home'=>'Home','whois'=>'WHOIS','contact'=>'Contact Us','lang'=>'中文'),
        'header' => array('title'=>'Premium Domain for Sale','subtitle'=>'优质域名出售','btn1'=>'Buy Now'),
        'purchase' => array(
            'options' => array(
                array('name'=>'Make Offer', 'desc'=>'Submit your offer for this domain', 'icon'=>'fa-tag', 'color'=>'indigo', 'price' => '$0', 'url'=>'https://tb.53kf.com/code/client/ec4153dd6ac57ca04aed366c376b8c3e5/1'),
                array('name'=>'Atom', 'desc'=>'Professional domain investment platform', 'icon'=>'fa-rocket', 'color'=>'purple', 'price' => '$'.$usdPrice, 'url'=>'https://www.atom.com/name/'),
                array('name'=>'4.cn', 'desc'=>'Professional domain trading platform', 'icon'=>'fa-shopping-bag', 'color'=>'green', 'url'=>'https://www.4.cn/search/detail/domain/?lang=en&domain=', 'price' => $discountPrice, 'discount' => '8'),
                array('name'=>'GoDaddy', 'desc'=>'World-renowned domain trading platform', 'icon'=>'fa-globe', 'color'=>'orange', 'price' => '$'.$usdPrice, 'url'=>'https://www.godaddy.com/forsale/'),
                array('name'=>'Aliyun', 'desc'=>'Leading cloud computing and domain service provider in China', 'icon'=>'fa-cloud', 'color'=>'blue', 'url'=>'https://mi.aliyun.com/detail/online.html?domainName=', 'price' => '¥'.$cnPrice, 'recommended' => true),
                array('name'=>'Escrow', 'desc'=>'Secure third-party escrow service', 'icon'=>'fa-lock', 'color'=>'teal', 'price' => '$'.$usdPrice, 'form' => '<form action="https://www.escrow.com/checkout" method="post" target="_blank"><input type="hidden" name="type" value="domain_name"><input type="hidden" name="non_initiator_email" value="8812182@qq.com"><input type="hidden" name="non_initiator_id" value="2374248"><input type="hidden" name="non_initiator_role" value="seller"><input type="hidden" name="title" value="B'.$domain.'"><input type="hidden" name="currency" value="USD"><input type="hidden" name="domain" value="'.$domain.'"><input type="hidden" name="price" value="'.$usdPrice.'"><input type="hidden" name="concierge" value="false"><input type="hidden" name="with_content" value="false"><input type="hidden" name="inspection_period" value="1"><input type="hidden" name="fee_payer" value="seller"><input type="hidden" name="return_url" value=""><input type="hidden" name="button_types" value="buy_now"><input type="hidden" name="auto_accept" value=""><input type="hidden" name="auto_reject" value=""><input type="hidden" name="item_key" value="undefined"><button class="w-full price-tag" type="submit">Buy Now ($'.$usdPrice.')</button><img src="https://t.escrow.com/1px.gif?name=bin&price='.$usdPrice.'&title='.$domain.'&user_id=2374248" style="display: none;"></form>'),
            )
        ),
        'contact' => array('title'=>'Domain Broker','name'=>'BaBu','position'=>'Professional Domain Broker','qq'=>'QQ: 8812182','wechat'=>'WeChat: 8812182','email'=>'Email: 8812182@qq.com'),
        'footer' => array('copyright'=>'All Rights Reserved')
    )
);
?>

<!DOCTYPE html>
<html lang="<?php echo $lang; ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?php echo htmlspecialchars($domain);?> BaBuPro | Premium domain for sale">
    <title><?php echo htmlspecialchars($domain);?> | Premium Domain</title>
    <!-- 修改为外部的 tailwindcss 文件路径 -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <!-- 修改为外部的 font-awesome 文件路径 -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#165DFF',     // 明亮专业的蓝色
                        secondary: '#FF5A36',   // 明亮橙红色（价格按钮）
                        accent: '#3A86FF',      // 稍浅的蓝色
                        dark: '#2B2D42',        // 深蓝灰色
                        light: '#F8F9FA',       // 极浅灰色背景
                        discount: '#FF4D4F',    // 红色折扣标签
                        consultation: '#00B42A', // 在线咨询按钮颜色
                        recommended: '#FFC107', // 推荐标签颜色
                        // 柔和配色
                        softTeal: '#4D96A9',
                        softIndigo: '#6B7280',
                        softPurple: '#8B5CF6',
                        softBlue: '#60A5FA',
                        softOrange: '#F97316',
                        softGreen: '#22C55E',
                    },
                    fontFamily: {
                        inter: ['Inter', 'system-ui', 'sans-serif'],
                    },
                }
            }
        }
    </script>
    <style type="text/tailwindcss">
        @layer utilities {
            .content-auto {
                content-visibility: auto;
            }
            .text-shadow {
                text-shadow: 0 1px 2px rgba(0,0,0,0.1);
            }
            .text-shadow-lg {
                text-shadow: 0 2px 4px rgba(0,0,0,0.12);
            }
            .transition-all-300 {
                transition: all 300ms ease-in-out;
            }
            .hover-scale {
                transition: transform 0.3s ease;
            }
            .hover-scale:hover {
                transform: scale(1.03);
            }
            .purchase-option {
                @apply bg-white border border-gray-100 rounded-xl p-5 shadow-sm hover:shadow-md transition-all hover-scale cursor-pointer;
            }
            .purchase-icon {
                @apply w-14 h-14 rounded-full flex items-center justify-center text-xl font-bold mr-4;
            }
            .purchase-name {
                @apply font-semibold text-dark text-xl mb-1;
            }
            .purchase-desc {
                @apply text-sm text-gray-500;
            }
            .purchase-btn {
                @apply py-3 rounded-lg hover:shadow-lg transition-all duration-300 hover:-translate-y-1 font-medium text-lg;
            }
            .price-tag {
                @apply inline-flex items-center justify-center bg-white border border-primary/20 text-primary px-4 py-2 rounded-lg shadow-sm text-lg font-medium hover:shadow-md transition-all duration-300;
            }
            .price-tag-aliyun {
                @apply inline-flex items-center justify-center bg-white border border-blue-200 text-blue-600 px-4 py-2 rounded-lg shadow-sm text-lg font-medium hover:shadow-md transition-all duration-300;
            }
            .price-tag-zero {
                @apply price-tag opacity-70;
            }
            .discount-badge {
                @apply inline-block bg-discount text-white text-xs font-bold px-2 py-1 rounded-full ml-1.5 transform -rotate-6 align-middle;
            }
        }
    </style>
</head>
<body class="font-inter bg-light text-dark min-h-screen flex flex-col">
    <!-- 导航栏 -->
    <nav class="bg-white shadow-md sticky top-0 z-50 transition-all duration-300">
        <div class="container mx-auto px-4 py-3 flex justify-between items-center">
            <div class="flex items-center">
                <i class="fa fa-globe text-primary text-2xl mr-2"></i>
                <span class="text-lg font-semibold text-dark"><?php echo htmlspecialchars($domain);?></span>
                <!-- 移除了顶部导航栏中的价格按钮 -->
            </div>
            
            <div class="hidden md:flex items-center space-x-4">
                <a href="#" class="text-gray-600 hover:text-primary transition-colors"><?php echo $i18n[$lang]['nav']['home']; ?></a>
                <a href="https://who.cx/<?php echo urlencode($domain); ?>" target="_blank" class="text-gray-600 hover:text-primary transition-colors">
                    <?php echo $i18n[$lang]['nav']['whois']; ?>
                </a>
                <a href="https://tb.53kf.com/code/client/ec4153dd6ac57ca04aed366c376b8c3e5/1" target="_blank" class="text-gray-600 hover:text-primary transition-colors"><?php echo $i18n[$lang]['nav']['contact']; ?></a>
                <a href="?lang=<?php echo $lang === 'zh' ? 'en' : 'zh'; ?>" class="bg-primary/10 text-primary px-3 py-1 rounded-full text-sm font-medium hover:bg-primary/20 transition-colors">
                    <?php echo $i18n[$lang]['nav']['lang']; ?>
                </a>
            </div>
            
            <div class="md:hidden flex items-center">
                <a href="?lang=<?php echo $lang === 'zh' ? 'en' : 'zh'; ?>" class="bg-primary/10 text-primary px-3 py-1 rounded-full text-sm font-medium hover:bg-primary/20 transition-colors mr-2">
                    <?php echo $i18n[$lang]['nav']['lang']; ?>
                </a>
                <button id="mobile-menu-btn" class="text-gray-600 focus:outline-none">
                    <i class="fa fa-bars text-xl"></i>
                </button>
            </div>
        </div>
        
        <!-- 移动端菜单 -->
        <div id="mobile-menu" class="hidden md:hidden bg-white shadow-lg absolute w-full z-50">
            <div class="container mx-auto px-4 py-3 flex flex-col space-y-3">
                <a href="#" class="text-gray-600 hover:text-primary transition-colors py-2"><?php echo $i18n[$lang]['nav']['home']; ?></a>
                <a href="https://who.cx/<?php echo urlencode($domain); ?>" target="_blank" class="text-gray-600 hover:text-primary transition-colors py-2">
                    <?php echo $i18n[$lang]['nav']['whois']; ?>
                </a>
                <a href="https://tb.53kf.com/code/client/ec4153dd6ac57ca04aed366c376b8c3e5/1" target="_blank" class="text-gray-600 hover:text-primary transition-colors py-2"><?php echo $i18n[$lang]['nav']['contact']; ?></a>
            </div>
        </div>
    </nav>

    <!-- 主横幅 -->
    <header class="relative overflow-hidden bg-gradient-to-r from-primary/90 to-accent/90 py-16 md:py-24">
        <div class="absolute inset-0 bg-[url('/banner.jpg')] bg-cover bg-center opacity-10"></div>
        <div class="container mx-auto px-4 relative z-10 text-center">
            <h1 class="text-[clamp(2rem,5vw,3.5rem)] font-bold text-white text-shadow-lg mb-4">
                <?php echo htmlspecialchars($domain);?>
            </h1>
            <p class="text-[clamp(1.1rem,2vw,1.5rem)] text-white/90 mb-8 max-w-2xl mx-auto">
                <strong class="font-semibold"><?php echo $i18n[$lang]['header']['title']; ?> <span class="hidden sm:inline">|</span> <?php echo $i18n[$lang]['header']['subtitle']; ?></strong>
            </p>
            <div class="flex flex-wrap justify-center gap-4">
                <?php if ($lang === 'en'): ?>
                <!-- 修改为 Atom 平台的链接 -->
                <a href="https://www.atom.com/name/<?php echo urlencode($domain); ?>" target="_blank" 
                   class="bg-white text-primary px-8 py-4 rounded-lg font-medium shadow-xl hover:shadow-2xl transition-all duration-300 hover:-translate-y-2 hover:bg-white/90">
                    <i class="fa fa-shopping-cart mr-2"></i><?php echo $i18n[$lang]['header']['btn1']; ?>
                </a>
                <?php else: ?>
                <a href="<?php echo 'https://mi.aliyun.com/detail/online.html?domainName=' . urlencode($domain); ?>" target="_blank" 
                   class="bg-white text-primary px-8 py-4 rounded-lg font-medium shadow-xl hover:shadow-2xl transition-all duration-300 hover:-translate-y-2 hover:bg-white/90">
                    <i class="fa fa-shopping-cart mr-2"></i><?php echo $i18n[$lang]['header']['btn1']; ?>
                </a>
                <?php endif; ?>
                <?php if ($domainPrice): ?>
                <a href="<?php echo $lang === 'zh' ? 'https://mi.aliyun.com/detail/online.html?domainName=' . urlencode($domain) : 'https://www.atom.com/name/' . urlencode($domain); ?>" target="_blank" 
                   class="bg-secondary text-white px-8 py-4 rounded-lg font-medium shadow-xl hover:shadow-2xl transition-all duration-300 hover:-translate-y-2 hover:bg-secondary/90">
                    <i class="fa fa-tag mr-2"></i><?php echo htmlspecialchars($domainPrice); ?>
                </a>
                <?php endif; ?>
                <!-- 新增的在线咨询按钮，仅在桌面端显示 -->
                <a href="https://tb.53kf.com/code/client/ec4153dd6ac57ca04aed366c376b8c3e5/1" target="_blank" 
                   class="hidden md:block bg-consultation text-white px-8 py-4 rounded-lg font-medium shadow-xl hover:shadow-2xl transition-all duration-300 hover:-translate-y-2 hover:bg-consultation/90">
                    <i class="fa fa-comment mr-2"></i><?php echo $lang === 'zh' ? '在线咨询' : 'Make an Offer'; ?>
                </a>
            </div>
        </div>
        <div class="absolute bottom-0 left-0 right-0 h-12 bg-gradient-to-t from-light to-transparent"></div>
    </header>

    <!-- 主内容区 -->
    <main class="flex-grow container mx-auto px-4 py-12">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <div class="lg:col-span-2 bg-white rounded-2xl shadow-lg p-8 hover-scale">
                <div class="mb-8">
                    <h3 class="text-lg font-semibold mb-4 flex items-center"><i class="fa fa-plug text-primary mr-2"></i><?php echo $lang === 'zh' ? '购买平台' : 'Purchase Platforms'; ?></h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <?php foreach ($i18n[$lang]['purchase']['options'] as $index => $option): ?>
                        <?php if (isset($option['form'])): ?>
                        <div class="purchase-option">
                            <div class="flex items-center mb-4">
                                <div class="purchase-icon bg-<?php echo $option['color']; ?>-50 text-<?php echo $option['color']; ?>-600">
                                    <i class="fa <?php echo $option['icon']; ?> text-2xl"></i>
                                </div>
                                <div class="flex-1">
                                    <h4 class="purchase-name">
                                        <?php echo $option['name']; ?>
                                        <?php if (isset($option['discount'])): ?>
                                        <span class="discount-badge"><?php echo $lang === 'zh' ? $option['discount'] . '优惠' : $option['discount'] . '% OFF'; ?></span>
                                        <?php endif; ?>
                                    </h4>
                                    <p class="purchase-desc"><?php echo $option['desc']; ?></p>
                                </div>
                            </div>
                            <?php echo $option['form']; ?>
                        </div>
                        <?php else: ?>
                        <a href="<?php echo $option['url'] . urlencode($domain); ?>" target="_blank" class="purchase-option">
                            <div class="flex items-center mb-4">
                                <div class="purchase-icon bg-<?php echo $option['color']; ?>-50 text-<?php echo $option['color']; ?>-600">
                                    <?php if ($option['name'] === '阿里云'): ?>
                                    <i class="fa fa-cloud text-blue-500 text-2xl"></i>
                                    <?php else: ?>
                                    <i class="fa <?php echo $option['icon']; ?> text-2xl"></i>
                                    <?php endif; ?>
                                </div>
                                <div class="flex-1">
                                    <h4 class="purchase-name">
                                        <?php echo $option['name']; ?>
                                        <?php if (isset($option['discount'])): ?>
                                        <span class="discount-badge"><?php echo $lang === 'zh' ? $option['discount'] . '优惠' : $option['discount'] . '% OFF'; ?></span>
                                        <?php endif; ?>
                                    </h4>
                                    <p class="purchase-desc"><?php echo $option['desc']; ?></p>
                                </div>
                            </div>
                            <div class="mt-3 flex justify-center">
                                <?php if ($option['name'] === '阿里云'): ?>
                                <span class="price-tag-aliyun"><?php echo $option['price']; ?></span>
                                <?php elseif ($option['price'] === '$0'): ?>
                                <span class="price-tag-zero"><?php echo $option['price']; ?></span>
                                <?php else: ?>
                                <span class="price-tag"><?php echo $option['price']; ?></span>
                                <?php endif; ?>
                            </div>
                        </a>
                        <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <div class="bg-gradient-to-br from-primary/90 to-accent/90 rounded-2xl shadow-xl text-white p-8 hover-scale">
                <h2 class="text-2xl font-bold mb-6 flex items-center"><i class="fa fa-user-circle-o mr-2"></i><?php echo $i18n[$lang]['contact']['title']; ?></h2>
                <div class="bg-white/10 backdrop-blur-sm rounded-xl p-6 mb-6">
                    <div class="text-center mb-6">
                        <img src="/wechat.jpg" alt="域名经纪：BaBu" class="w-40 h-40 rounded-full mx-auto border-4 border-white/20 shadow-lg">
                        <h3 class="text-xl font-semibold mt-3"><?php echo $i18n[$lang]['contact']['name']; ?></h3>
                        <p class="text-white/80 text-sm"><?php echo $i18n[$lang]['contact']['position']; ?></p>
                    </div>
                    
                    <div class="space-y-4">
                        <a href="http://wpa.qq.com/msgrd?v=3&uin=8812182&site=qq&menu=yes" target="_blank" class="flex items-center bg-white/10 hover:bg-white/20 p-3 rounded-lg transition-all">
                            <div class="w-10 h-10 rounded-full bg-blue-500 flex items-center justify-center mr-3"><i class="fa fa-qq"></i></div>
                            <span><?php echo $i18n[$lang]['contact']['qq']; ?></span>
                        </a>
                        
                        <a href="javascript:void(0)" class="flex items-center bg-white/10 hover:bg-white/20 p-3 rounded-lg transition-all">
                            <div class="w-10 h-10 rounded-full bg-green-500 flex items-center justify-center mr-3"><i class="fa fa-weixin"></i></div>
                            <span><?php echo $i18n[$lang]['contact']['wechat']; ?></span>
                        </a>
                        
                        <a href="mailto:8812182@qq.com" class="flex items-center bg-white/10 hover:bg-white/20 p-3 rounded-lg transition-all">
                            <div class="w-10 h-10 rounded-full bg-red-500 flex items-center justify-center mr-3"><i class="fa fa-envelope-o"></i></div>
                            <span><?php echo $i18n[$lang]['contact']['email']; ?></span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- 页脚 -->
    <footer class="bg-dark text-white/80 py-8">
        <div class="container mx-auto px-4">
            <div class="flex flex-col md:flex-row justify-between items-center">
                <div class="mb-4 md:mb-0">
                    <div class="flex items-center">
                        <i class="fa fa-globe text-primary text-xl mr-2"></i>
                        <span class="text-lg font-semibold text-white"><?php echo htmlspecialchars($domain);?></span>
                    </div>
                    <p class="text-xs mt-1">优质域名 | Premium domain for sale. Contact us for inquiries.</p>
                </div>
                
                <div class="flex space-x-6 mb-4 md:mb-0">
                    <a href="#" class="text-white/70 hover:text-white transition-colors"><?php echo $i18n[$lang]['nav']['home']; ?></a>
                    <a href="https://who.cx/<?php echo urlencode($domain); ?>" target="_blank" class="text-white/70 hover:text-white transition-colors"><?php echo $i18n[$lang]['nav']['whois']; ?></a>
                    <a href="https://tb.53kf.com/code/client/ec4153dd6ac57ca04aed366c376b8c3e5/1" target="_blank" class="text-white/70 hover:text-white transition-colors"><?php echo $i18n[$lang]['nav']['contact']; ?></a>
                </div>
                
                <div class="flex space-x-4">
                    <a href="https://mi.aliyun.com" target="_blank" class="text-white/70 hover:text-white transition-colors">
                        <i class="fa fa-cloud"></i>
                    </a>
                    <a href="https://www.4.cn" target="_blank" class="text-white/70 hover:text-white transition-colors">
                        <i class="fa fa-shopping-bag"></i>
                    </a>
                    <a href="https://www.escrow.com" target="_blank" class="text-white/70 hover:text-white transition-colors">
                        <i class="fa fa-lock"></i>
                    </a>
                    <a href="https://www.godaddy.com" target="_blank" class="text-white/70 hover:text-white transition-colors">
                        <i class="fa fa-globe"></i>
                    </a>
                </div>
            </div>
            <div class="mt-6 text-center text-xs text-white/50">
                <p><?php echo $i18n[$lang]['footer']['copyright']; ?> &copy; <?php echo date('Y'); ?> <?php echo htmlspecialchars($domain);?> 保留所有权利</p>
            </div>
        </div>
    </footer>

    <script>
        // 移动端菜单切换
        document.getElementById('mobile-menu-btn').addEventListener('click', function() {
            const mobileMenu = document.getElementById('mobile-menu');
            mobileMenu.classList.toggle('hidden');
        });

        // 导航栏滚动效果
        window.addEventListener('scroll', function() {
            const nav = document.querySelector('nav');
            if (window.scrollY > 50) {
                nav.classList.add('py-2');
                nav.classList.remove('py-3');
            } else {
                nav.classList.add('py-3');
                nav.classList.remove('py-2');
            }
        });
    </script>
    <script>(function() {var _53code = document.createElement("script");_53code.src = "https://tb.53kf.com/code/code/ec4153dd6ac57ca04aed366c376b8c3e5/1";var s = document.getElementsByTagName("script")[0]; s.parentNode.insertBefore(_53code, s);})();</script>
    <script charset="UTF-8" id="LA_COLLECT" src="//sdk.51.la/js-sdk-pro.min.js"></script>
<script>LA.init({id:"JiuIPgS4XXVNHelS",ck:"JiuIPgS4XXVNHelS"})</script>
</body>
</html>

<?php
function is_mobile() {
    return preg_match("/(android|avantgo|blackberry|bolt|boost|cricket|docomo|fone|hiptop|mini|mobi|palm|phone|pie|tablet|up\.browser|up\.link|webos|wos)/i", $_SERVER["HTTP_USER_AGENT"]);
}
?>