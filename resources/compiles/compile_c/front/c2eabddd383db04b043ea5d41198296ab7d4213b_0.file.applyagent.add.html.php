<?php
/* Smarty version 3.1.30, created on 2017-08-06 10:54:12
  from "/home/bendishangjia.com/www/resources/views/templates/front/applyagent.add.html" */

/* @var Smarty_Internal_Template $_smarty_tpl */
if ($_smarty_tpl->_decodeProperties($_smarty_tpl, array (
  'version' => '3.1.30',
  'unifunc' => 'content_598684d40d1735_90415068',
  'has_nocache_code' => false,
  'file_dependency' => 
  array (
    'c2eabddd383db04b043ea5d41198296ab7d4213b' => 
    array (
      0 => '/home/bendishangjia.com/www/resources/views/templates/front/applyagent.add.html',
      1 => 1501988027,
      2 => 'file',
    ),
  ),
  'includes' => 
  array (
    'file:public.header.html' => 1,
    'file:public.footer.html' => 1,
  ),
),false)) {
function content_598684d40d1735_90415068 (Smarty_Internal_Template $_smarty_tpl) {
$_smarty_tpl->_subTemplateRender("file:public.header.html", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array(), 0, false);
?>

<?php echo '<script'; ?>
 src="/style/js/jquery/jquery.min.js"><?php echo '</script'; ?>
>
<style type="text/css">
    #category input[type="text"]{width:80%; border-bottom: 1px solid #ccc;background-color: #fff;}
    #category div{margin-top: 5px;}
    #category div p{margin-top: 5px;}
    #category #city [type="text"]{width:20%; border-bottom: 1px solid #ccc;background-color: #fff;}
    #category #city select{width:20%; border-bottom: 1px solid #ccc;background-color: #fff;}
    #sure{width:90%;height: 30px;margin: 10px auto;}
    #sure #btn{border-radius: 3px;background-color: #f60;color: #fff;}
</style>
<form action="" method="post" name="form1" id="form1">
<div id="user-basic">
    <div class="return" onclick="javascript:history.go(-1);"><i></i></div>
    <h1>代理商申请</h1>
</div>
<div id="category">
    <center><h2>本地商家网区域代理协议</h2></center>
    <div>
        <p>甲方：浙江聚众网络科技有限公司</p>
        <p>乙方：<input type="text" name="Party_b" id="Party_b" style="width: 80%"></p>
    </div>
    <div>
        <h2><b>第一章 词语定义与解释</b></h2>
        <p>1.1本地商家网：甲方制作并拥有全部知识产权的本地商家网网应用平台、本地商家网官网、商户后台管理系统、本地商家网注册商标以及以之为载体搭建的本地商家网商业模式。</p>
        <p>1.2本地商家网网平台：平台入口是本地商家网官网（网址www.bendishangjia.com），代理商可以利用本地商家网平台进行实体商家的广告传播服务。</p>
        <p>1.3商家：在本地商家网后台管理系统登记注册并经乙方开通后台权限的商户，商户指从事商品经营或营利性服务的法人、其他经济组织和个人。</p>
        <p>1.4消费者：使用本地商家网网用户，消费者通过本地商家网网获取需要的商家信息及采购行为。</p>
        <p>1.5代理区域：指乙方使用本地商家网平台从事本协议指定合作的区域，代理区域与行政区域相对应，以《行政区域界线管理条例》规定的行政区域界限为确定依据。代理商仅能在自己的代理区域内从事代理事宜。</p>
        <p>1.6软件使用维护费：指乙方为从事代理事项，使用甲方开发的本地商家网平台，甲方为此提供的本地商家网平台软件日常维护和技术指导所产生费用。</p>
        <p>1.7信息费用：指代理区域内的商家利用本地商家网平台提供的大数据服务给特定消费者精准推送商品信息时应支付给甲方、乙方的费用。</p>
    </div>
    <div>
        <h2><b>第二章 合作的前提和基础</b></h2>
        <p>2.1甲乙双方均是脚踏实地的做事者，承诺守法经营。</p>
        <p>2.2甲乙双方以做大做强本地商家网为己任，特别是乙方肩负着拓展商户和消费者两端的重大责任，让更多的商户加入本地商家网平台，利用本地商家网平台推动商家的信息传播及带动业绩提升，让更多的商家及消费者使用本地商家网平台，利用本地商家网为商家获取更多商业机会。</p>
        <p>2.3为了维护本地商家网全国商业模式的权威性、统一性、规范性，并考虑到不同行业差异性，甲乙双方同意在合作期间商户应付的佣金金额以及各个受益人之间的相互收益比例由甲方统一决定，甲乙双方遵照执行。</p>
    </div>
    <div id="city">
        <h2><b>第三章 本地商家网区域代理</b></h2>
        <p>3.1代理区域：</p>
        <p style="color:red">中国
            <select onchange="getCity(this)" name="pross" id="pross">
                <option value="0">请选择</option>
                <?php
$_from = $_smarty_tpl->smarty->ext->_foreach->init($_smarty_tpl, $_smarty_tpl->tpl_vars['province']->value, 'item');
if ($_from !== null) {
foreach ($_from as $_smarty_tpl->tpl_vars['item']->value) {
?>
                <option value="<?php echo $_smarty_tpl->tpl_vars['item']->value['id'];?>
"><?php echo $_smarty_tpl->tpl_vars['item']->value['name'];?>
</option>
                <?php
}
}
$_smarty_tpl->smarty->ext->_foreach->restore($_smarty_tpl);
?>

            </select>
            省
            <select id="citys" onchange="getArea(this)" name="citys">
                <option value="0">请选择</option>
            </select>
            市
            <select id="area" name="area">
                <option value="0">请选择</option>
            </select>
            区（县)
        <p>合作期限： 3  年，到期后在同等条件下乙方享有优先续签权。<em style="color:red">审核通过后会有您的专职客服告知您的账号以及密码并签订正式合同。</em></p>
        <p>3.2乙方只可以与指定代理区域内的联盟商户签约；如果联盟商户的工商注册营业地址与实际营业地址不相同，一律以实际营业地址为唯一依据；如果一个商户有几个实体店或者分店且实际营业地址坐落在不同的代理区域，那么乙方有且仅能与坐落在本代理区域内的实体店或者分店签约。</p>
        <p>3.3乙方负责与代理区域内的商户建立合作契约关系。自本协议签订之日起30 天内完成商家数字达到2000家，认证商家30家以上，若未达到，甲方有权解除本协议，终止双方的合作，具体目标如下：</p>
        <p>一线城市：壹年签约会员商户数目标：>=2500家；</p>
        <p>二线城市：壹年签约会员商户数目标：>=2000家；</p>
        <p>三线城市：壹年签约会员商户数目标：>=1500家；</p>
        <p>四线城市：壹年签约会员商户数目标：>=1000家；</p>
        <p>3.4乙方须支付软件使用维护费 叁万元整  元（  30000.00  元），签约之日支付给甲方，乙方签约后依支持本地商家网品牌在乙方代理区域使用，如因乙方行为造成本地商家网不能使用，乙方承担甲方的一切损失及法律义务。</p>
        <p>3.5代理收益的支付方式为：按照代理区域收取商家本地商家网认证，信息查看费、本地信息发布费及异地信息发布费总收益的80%给乙方（含合伙人收益），以分润的形式结算到本地商家网代商理后台管理系统中的乙方账户中，乙方就账户中的分润可以向甲方申请提现，甲方会在  叁个  个工作日将现金汇入乙方在本地商家网后台管理系统所绑定的银行卡内（或微信、支付宝、企业账户）。合作收益不计息，可能产生的支付手续费由乙方承担。乙方应当依法纳税，并开具相应金额发票给甲方。</p>
        <p>3.6乙方承诺确保本代理区域内认证商家会员目标，自本协议签订之日第91天开始连续三个月增长率不低于前一月的20%，如果低于20%，甲方将有权取消乙方的代理权限，终止合同。</p>
    </div>
    <div>
        <h2>第四章 双方的权利义务</h2>
        <p>4.1甲方的权利和义务</p>
        <p>4.1.1甲方有权对乙方的经营活动进行帮助和检查监督，并提出改进意见。</p>
        <p>4.1.2甲方有《本地商家网区域代理合同》、本地商家网平台用户注册协议、本地商家网后台管理系统注册协议的制定修改权。甲方拥有对用户获得获取信息支付信息费的金额及分配比例及细则调整的权利。</p>
        <p>4.1.3甲方需及时给乙方支付代理合作收益，支持乙方的业务发展。</p>
        <p>4.2乙方的权利和义务</p>
        <p>4.2.1乙方有权利自主聘用工作人员，独立经营，自负盈亏。</p>
        <p>4.2.2乙方有权享受代理合作收益</p>
        <p>4.2.3乙方可以请求甲方提供技术支持。</p>
        <p>4.2.4乙方无条件地认可本地商家网商业模式，坚守并维护双方合作的前提和基础。</p>
        <p>4.2.5未经正式授权，乙方不得以“办事处”或“总代理”等具有误导性、垄断性、排他性和其它未经甲方授权的名义进行广告宣传及商业活动。乙方不得做出任何引人误解或引起混淆的行为，使他人误以为乙方是甲方子公司或分公司、关联公司或其他实质性关系单位，例如企业名称或字号中不得出现“本地商家网”等引人误解其为甲方分公司或分支机构的字样。</p>
        <P>4.2.6乙方与甲方的其他代理商之间不得进行恶性竞争或者其他不正当竞争，包括但不限于跨区域发展商户、发布谣言或者不实信息、诋毁甲方或者其他合作方声誉。</P>
        <P>4.2.7乙方不得未经甲方同意而擅自转让合作权，也不得以任何方式出租、倒卖合作权或者本地商家网平台使用权。</P>
        <P>4.2.8乙方不得以甲方名义和理由向注册商户直接收取费用。</P>
        <P>4.2.9乙方负责对注册商户进行本地商家网后台管理系统的使用培训，解答客户提出的各种问题，并负责对注册商户进行诚信监督，包括但不限于：督促商户及时提供营业执照、组织机构代码证、税务登记证、银行开户许可证、卫生许可证等相关资料，协助甲方对申请成为注册商户的商户主体身份进行审查和登记；督促注册商户遵守《网络交易管理办法》、《消费者权益保护法》、《反不正当竞争法》等法律法规；协助甲方处理消费者投诉。</P>
        <P>4.2.10在合作期间内，乙方不得和任何与甲方构成直接商业竞争关系的企业、商业机构或者组织进行相同或者类似本协议内容的合作。</P>
        <P>4.2.11在本协议有效期内及本协议终止或者解除后，乙方承诺乙方及其工作人员严格保守甲方的商业机密（包括但不限于本协议内容、本地商家网的商业模式、本地商家网后台管理信息和技术信息、区域代理收益信息等一切相关信息或者资料）。</P>
        <P>4.2.12乙方不得违反公司意愿，私自承诺和虚吹公司其它政策，不得对签约商家进行虚假承诺。</P>
        <P>4.2.13若乙方违反本协议项下乙方义务，甲方有权随时单方解除本协议，终止合作，并要求赔偿损失。</P>
    </div>
    <div>
        <h2>第五章  协议的终止和解除</h2>
        <p>5.1在本协议履行期间，甲方可以按照本协议约定行使解除权。如果乙方需要解除本协议，应提前一个月书面通知甲方，经甲方同意后双方可以提前终止本协议。</p>
        <p>5.2乙方自本协议解除或者终止之日起不再享受代理合作收益，本地商家网后台管理系统的乙方权限将被收回，须由甲方接收和处理该代理区域内的全部商家，如甲方需要接收部分乙方工作人员的，乙方应当给予支持。</p>
    </div>
    <div>
        <h2>第六章  其他约定</h2>
        <p>6.1乙方如是自然人，应成立公司或企业，领取营业执照，乙方作为公司企业的负责人；如果乙方是公司，那就由乙方指定并经过甲方备案的自然人作为代理合作的负责人。</p>
        <p>6.2因履行本协议发生的纠纷，由甲方住所地人民法院管辖。</p>
        <p>6.3本协议中所出现的各方地址、电话、电子邮箱、QQ、微信号为双方发出通知和接受通知的有效联系方式和通讯地址。一方如变更通讯地址或联系方式，应自变更之日起三日内，将变更后的地址、联系方式通知另一方，否则变更方应对此造成的一切后果承担责任。</p>
        <p>6.4本协议一式两份，双方各执一份，附件具有同等法律效力。</p>
        <p>6.5乙方提供营业执照复印件盖章、签约人身份证盖章。</p>
    </div>
    <div style="color: red">
        <h2>风险告知与解释合同确认书：</h2>
        <p>在签署《本地商家网代理区域合同》之前，公司已将其经营方针、经营范围、商业模式明确告诉我方，向我方逐条详细解释了《本地商家网代理区域合同》，告知了我方合作可能产生的各种风险，我方对贵司的经营理念、商业模式和上述协议、管理细则已充分理解，并无任何的误解，也愿意承担所有合作风险。</p>
        <p>&nbsp;&nbsp;&nbsp;&nbsp;特此确认。</p>
        <!-- <p style="float: right;">代理区域申请人：<input type="text" name="" style="width: 30%"/></p> -->
        <br/>
        <br/>
    </div>
    <div>
        <p>甲方（盖章） ：浙江聚众网络科技有限公司</p>
        <!-- <p>推荐人：<input type="text" name=""></p>
        <p>地址：浙江嘉善大道902号 301室（温州大厦）</p>
        <p>电话：0573-82111168、4001101633</p>
        <p>电子邮箱：349633191@qq.com</p>
        <p>QQ: 349633191 </p> -->
        <!-- <p>日期：<input type="date" name="" style="width: 80%" /></p> -->
    </div>
    <div>
        <p>乙方(签名):<input type="text" name="signature" id="signature" /></p>
        <!-- <p>签约人：<input type="text" name=""></p> -->
        <p>身份证：<input type="text" name="id_card" id="id_card"></p>
        <p>手机号：<input type="text" name="mobile" id="mobile"></p>
        <p>地 &nbsp;&nbsp;址：<input type="text" name="address" id="address"></p>
        <p>邮 &nbsp;&nbsp;箱：<input type="text" name="email" id="email" placeholder="审核通过后确认信息发送到此邮箱"></p>
        <p>推荐人：<input type="text" name="referees" id="referees"></p>
        <p>备 &nbsp;&nbsp;注：<input type="text" name="notes" id="notes" placeholder="例如：我要代理嘉兴市"></p>
        <p>日 &nbsp;&nbsp;期：<input type="date" name="dates" style="width: 40%" id="dates"/></p>
        <br/>
        <p style="color:red;">PS：成为代理商预付金额人民币3000.00元</p>
    </div>
    <br/>
    <div id="sure"><button type="button" id="btn" onclick="apply_add();">确 认</button></div>
    <br/>
    <br/>
</div>
</form>
<?php echo '<script'; ?>
 type="text/javascript">
    function getCity(t){//获取市
        var id = $(t).val();
        $.ajax({
            type:'POST',
            url:'index.php?a=city&c=applyagent',
            data:{id:id},
            dataType:'json',
            success: function(o) {
                if(o.isok=='true'){
                    if (o.datas.length > 0){
                        var jsonListObj = o.datas;
                        insertListDiv(jsonListObj);
                    }else{
                        
                    }
                }else{

                }
            },
        });
    }
    function insertListDiv(json){//将数据添加到页面中
        var $mainDiv = $("#citys");
        var html='';
        $mainDiv.html('');
        html+='<option value="0">请选择</option>';
        for (var i=0; i<json.length; i++){
            html+='<option value="'+json[i].id+'">'+json[i].name+'</option>';
        }
        $mainDiv.append(html);
    }
    function getArea(t){//获取县
        var id = $(t).val();
        $.ajax({
            type:'POST',
            url:'index.php?a=area&c=applyagent',
            data:{id:id},
            dataType:'json',
            success: function(o) {
                if(o.isok=='true'){
                    if (o.datas.length > 0){
                        var jsonListObj = o.datas;
                        insertListArea(jsonListObj);
                    }else{
                        
                    }
                }else{

                }
            },
        });
    }
    function insertListArea(json){//将数据添加到页面中
        var $mainDiv = $("#area");
        var html='<option value="0">请选择</option>';
        $mainDiv.html('');
        for (var i=0; i<json.length; i++){
            html+='<option value="'+json[i].id+'">'+json[i].name+'</option>';
        }
        $mainDiv.append(html);
    }
    function apply_add(){//数据提交
        var  Party_b = $('#Party_b').val();
        var  pross = $("#pross option:selected").val();//省
        var  citys = $("#citys option:selected").val();//市
        var  area = $("#area option:selected").val();//区县
        var  signature = $("#signature").val();//乙方签名
        var  id_card = $("#id_card").val();//身份证
        var  mobile = $("#mobile").val();//手机号
        var  address = $("#address").val();//地址
        var  email = $("#email").val();//邮箱
        var  referees = $("#referees").val();//推荐人
        var  dates = $("#dates").val();//日期
        if(Party_b == 0){
            Message.showMessage('公司名称不能为空！');
            return false;
        }
        if(pross == 0){
            Message.showMessage('代理省不能为空！');
            return false;
        }
        /*if(!citys){
            Message.showMessage('所在市不能为空！');
            return false;
        }
        if(!area){
            Message.showMessage('所在区/县不能为空！');
            return false;
        }*/
        if(!signature){
            Message.showMessage('乙方签名不能为空！');
            return false;
        }
        if(!id_card){
            Message.showMessage('身份证号不能为空！');
            return false;
        } 
        if (!/^[1-9]{1}[0-9]{14}$|^[1-9]{1}[0-9]{16}([0-9]|[xX])$/.test(id_card)){
            Message.showMessage('您输入的身份证号码不正确！');
            return false;
        }
        if(!mobile){
            Message.showMessage('手机号不能为空！');
            return false;
        }
        if(!/^(((13[0-9]{1})|(15[0-9]{1})|(18[0-9]{1}))+\d{8})$/.test(mobile)){
            Message.showMessage('手机号格式不正确！');
            return false;
        }
        if(!address){
            Message.showMessage('详细地址不能为空！');
            return false;
        }
        if(!email){
            Message.showMessage('邮箱不能为空！');
            return false;
        }
        if(!/^([a-zA-Z0-9_-])+@([a-zA-Z0-9_-])+(.[a-zA-Z0-9_-])+/.test(email)){
            Message.showMessage('邮箱格式不正确！');
            return false;
        }
        if(!dates){
            Message.showMessage('电子合同填写日期不能为空！');
            return false;
        }
        $.ajax({
            type:'POST',
            url:'',
            data:$('form').serialize(),
            dataType:'json',
            success: function(o) {
                if(o.isok=='true'){
                    Message.showNotify(""+ o.data+"", 3000);
                    setTimeout("window.location.href='"+o.url+"'",1600);
                }else{
                    Message.showNotify(''+o.data+'', 3000);
                    setTimeout("window.location.href='"+o.url+"",1600);
                }
            },
        });
    }
    
<?php echo '</script'; ?>
>
<?php $_smarty_tpl->_subTemplateRender("file:public.footer.html", $_smarty_tpl->cache_id, $_smarty_tpl->compile_id, 0, $_smarty_tpl->cache_lifetime, array(), 0, false);
}
}
