<?php
// +----------------------------------------------------------------------
// | HisiPHP框架[基于ThinkPHP5开发]
// +----------------------------------------------------------------------
// | Copyright (c) 2016-2018 http://www.hisiphp.com
// +----------------------------------------------------------------------
// | HisiPHP提供个人非商业用途免费使用，商业需授权。
// +----------------------------------------------------------------------
// | Author: 橘子俊 <364666827@qq.com>，开发者QQ群：50304283
// +----------------------------------------------------------------------
namespace app\admin\validate;

use think\Validate;
use app\admin\model\AdminMenu as MenuModel;

/**
 * 菜单验证器
 * @package app\admin\validate
 */
class AdminMenu extends Validate
{
    //定义验证规则
    protected $rule = [
        'url|菜单链接' => 'require|checkUrl:thinkphp',
        'module|所属模块' => 'require',
        'pid|所属菜单'    => 'require',
        'title|菜单名称'  => 'require',
    ];

    //定义验证提示
    protected $message = [
        'module.require' => '请选择所属模块',
        'pid.require'    => '请选择所属菜单',
        'url.require'    => '菜单链接已存在',
    ];

    // 自定义菜单链接验证规则
    protected function checkUrl($value, $rule, $data)
    {
        return true;
        $map = [];
        $map['url'] = $value;
        $map['param'] = $data['param'];
        if (isset($data['id']) && $data['id'] > 0) {
            $map['id'] = ['neq', $data['id']];
        }
        $res = MenuModel::where($map)->find();

        if ($data['param']) {
            return $res ? '菜单链接和扩展参数已存在！' : true;
        }
        return $res ? '菜单链接已存在！' : true;
    }
}
