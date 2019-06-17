<?php

return [
    'characters' => '2345678abcdefhjkmnpqrtuvwxyABCDEFGHKLMNPQRTUVWXY',
    'charactersZh' => '们以我到他会作时要动国产的一是工就年阶义发成部民可出能方进在了不和有大这主中人上为来分生对于学下级地个用同行面说种过命度革而多子后自社加小机也经力线本电高量长党得实家定深法表着水理化争现所二起政三好十战无农使性前等反体合斗路图把结第里正新开论之物从当两些还天资事队批点育重其思与间内去因件日利相由压员气业代全组数果期导平各基或月毛然如应形想制心样干都向变关问比展那它最及外没看治提五解系林者米群头意只明四道马认次文通但条较克又公孔领军流入接席位情运器并飞原油放立题质指建区验活众很教决特此常石强极土少已根共直团统式转别造切九你取西持总料连任志观调七么山程百报更见必真保热委手改管处将修支识病象几先老光专什六型具示复安带每东增则完风回南广劳轮科北打积车计给节做务被整联步类集号列温装即毫知轴研单色坚据速防史拉世设达尔场织历花受求传口断况采精金界品判参层止边清至万确究书术状厂须离再目海交权且儿青才证低越际八试规斯近注办布门铁需走议县兵固除般引齿千截野遗静谋弄挂课镇妄盛耐援扎虑键归符庆聚绕摩忙舞遇索顾胶羊湖钉仁音迹碎伸灯避泛亡答勇频皇柳哈揭甘诺概宪浓岛袭谁洪谢炮浇斑讯懂灵蛋闭孩释乳巨徒私银伊景坦累匀霉杜乐勒隔弯绩招绍胡呼痛峰零柴簧午跳居尚丁秦稍追梁折耗碱殊岗挖氏刃剧堆赫荷胸衡勤膜篇登驻案刊秧缓凸役剪川雪链渔啦脸户洛孢勃盟买杨宗焦赛旗滤硅炭股坐蒸凝竟陷枪黎救冒暗洞犯筒您宋弧爆谬涂味津臂障褐陆啊健尊豆拔莫抵桑坡缝警挑污冰柬嘴啥饭塑寄赵喊垫丹渡耳刨虎笔稀昆浪萨茶滴浅拥穴覆伦娘吨浸袖珠雌妈紫戏塔锤震岁貌洁剖牢锋疑霸闪埔猛诉刷狠忽灾闹乔启逆卸航衣孙龄岭骗休借',

    'default' => [
        'name' => 'captcha', //不填写则默认session名称为:captcha
        'length' => 5,
        'width' => 160,
        'height' => 50,
        'quality' => 90,
        'noise' => 60,     //噪点数量
        'angle' => 1,      //字体角度(0:则随机角度)
        'distort' => true, //开启图像扭曲度
        'distortType' => 2, //图像扭曲算法
        'distortScale' => 2.0, //图像扭曲度(仅distortType=1有效)
        'lines' => 0,     //干扰线数量(0:则不生成)
        'lineThickness' => mt_rand(1, 3), //干扰线粗细
        'curve' => false,  //一条干扰曲线
        'math' => false,   //生成数学验证码
        'zh' => false,     //生成中文验证码
        'imageBg' => false,  //是否使用图像背景
        //'characters' => '23456789' //自定义验证码字符，中文也适用
        //'fontColors' => ['#2c3e50'] //指定字体颜色
    ],

    'number' => [
        'name' => 'number',
        'length' => mt_rand(4, 5),
        'width' => 120,
        'height' => 40,
        'characters' => '23456789',
        'lines' => mt_rand(1, 3),     //干扰线数量(0:则不生成)
        'lineThickness' => mt_rand(1, 3),
        'distortType' => 1, //图像扭曲算法
        'distortScale' => 2.0, //图像扭曲度(仅distortType=1有效)
    ],

    'en' => [
        'length' => 5,
        'width' => 160,
        'height' => 50,
        'angle' => 1,
        'characters' => 'abcdefghkmnpqrstuvwxy',
        'distort' => true, //开启图像扭曲度
        'distortType' => 2, //图像扭曲算法
        'lines' => mt_rand(1, 3),     //干扰线数量(0:则不生成)
        'lineThickness' => mt_rand(1, 3),
    ],

    'zh' => [
        'length' => 4,
        'width' => 160,
        'height' => 50,
        'quality' => 90,
        'noise' => 60,     //噪点数量
        'zh' => true,
        'lines' => mt_rand(1, 3),     //干扰线数量(0:则不生成)
        'lineThickness' => mt_rand(1, 3),
    ],

    'math' => [
        'width' => 160,
        'height' => 50,
        'quality' => 90,
        'noise' => 60,     //噪点数量
        'math' => true,
        'lines' => mt_rand(1, 3),     //干扰线数量(0:则不生成)
        'lineThickness' => mt_rand(1, 3),
        'distort' => true, //开启图像扭曲度
        'distortType' => 1, //图像扭曲算法
        'distortScale' => 1.2, //图像扭曲度(仅distortType=1有效)
    ],

    'imgbg' => [
        'width' => 160,
        'height' => 50,
        'quality' => 90,
        'noise' => 60,     //噪点数量
        'imageBg' => true,  //是否使用图像背景
        'lines' => mt_rand(3, 4),     //干扰线数量(0:则不生成)
        'lineThickness' => mt_rand(1, 3),
        'fontColors'=> ['#2c3e50', '#c0392b', '#16a085', '#c0392b', '#8e44ad', '#303f9f', '#f57c00', '#795548'],
    ],


];