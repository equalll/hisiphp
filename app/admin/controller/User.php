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
namespace app\admin\controller;

use app\admin\model\AdminUser as UserModel;
use app\admin\model\AdminRole as RoleModel;
use app\admin\model\AdminMenu as MenuModel;
use think\Validate;

/**
 * 后台用户、角色控制器
 * @package app\admin\controller
 */
class User extends Admin
{
    public $tab_data = [];
    /**
     * 初始化方法
     */
    protected function _initialize()
    {
        parent::_initialize();

        $tab_data['menu'] = [
            [
                'title' => '管理员角色',
                'url' => 'admin/user/role',
            ],
            [
                'title' => '系统管理员',
                'url' => 'admin/user/index',
            ],
        ];
        $this->tab_data = $tab_data;
    }

    /**
     * 用户管理
     * @author 橘子俊 <364666827@qq.com>
     * @return mixed
     */
    public function index($q = '')
    {
        $sqlmap = [];
        if ($q) {
            $sqlmap['username'] = ['like', '%'.$q.'%'];
        }
        $data_list = UserModel::where($sqlmap)->paginate();
        // 分页
        $pages = $data_list->render();
        $tab_data = $this->tab_data;
        $tab_data['current'] = url('');
        $this->assign('role_list', RoleModel::getAll());
        $this->assign('data_list', $data_list);
        $this->assign('tab_data', $tab_data);
        $this->assign('tab_type', 1);
        $this->assign('pages', $pages);
        return $this->fetch();
    }

    /**
     * 添加用户
     * @author 橘子俊 <364666827@qq.com>
     * @return mixed
     */
    public function addUser()
    {
        if ($this->request->isPost()) {
            $data = $this->request->post();
            // 验证
            $result = $this->validate($data, 'AdminUser');
            if($result !== true) {
                return $this->error($result);
            }
            unset($data['id']);
            $data['last_login_ip'] = '';
            if (!UserModel::create($data)) {
                return $this->error('添加失败！');
            }
            return $this->success('添加成功。');
        }

        $this->assign('role_option', RoleModel::getOption());
        return $this->fetch('userform');
    }

    /**
     * 修改用户
     * @param int $id
     * @author 橘子俊 <364666827@qq.com>
     * @return mixed
     */
    public function editUser($id = 0)
    {
        if ($id == 1 && ADMIN_ID != $id) {
            return $this->error('禁止修改超级管理员！');
        }
        if ($this->request->isPost()) {
            $data = $this->request->post();
            // 超级管理员角色不可更改角色分组，当前登陆用户不可更改自己的分组角色
            if ($data['id'] == 1 || ADMIN_ROLE == $data['role_id']) {
                unset($data['role_id']);
            }

            // 验证
            $result = $this->validate($data, 'AdminUser.update');
            if($result !== true) {
                return $this->error($result);
            }

            if ($data['password'] == '') {
                unset($data['password']);
            }

            if (!UserModel::update($data)) {
                return $this->error('修改失败！');
            }
            return $this->success('修改成功。');
        }

        $row = UserModel::where('id', $id)->field('id,username,role_id,nick,email,mobile,status')->find()->toArray();
        $this->assign('role_option', RoleModel::getOption($row['role_id']));
        $this->assign('data_info', $row);
        return $this->fetch('userform');
    }

    /**
     * 修改个人信息
     * @author 橘子俊 <364666827@qq.com>
     * @return mixed
     */
    public function info()
    {
        if ($this->request->isPost()) {
            $data = $this->request->post();
            $data['id'] = ADMIN_ID;
            // 防止伪造
            unset($data['role_id'], $data['status']);

            if ($data['password'] == '') {
                unset($data['password']);
            }
            // 验证
            $result = $this->validate($data, 'AdminUser.info');
            if($result !== true) {
                return $this->error($result);
            }

            if (!UserModel::update($data)) {
                return $this->error('修改失败！');
            }
            return $this->success('修改成功。');
        }

        $row = UserModel::where('id', ADMIN_ID)->field('username,nick,email,mobile')->find()->toArray();
        $this->assign('data_info', $row);
        return $this->fetch();
    }

    /**
     * 删除用户
     * @param int $id
     * @author 橘子俊 <364666827@qq.com>
     * @return mixed
     */
    public function delUser()
    {
        $ids   = input('param.ids/a');
        $model = new UserModel();
        if ($model->del($ids)) {
            return $this->success('删除成功。');
        }
        return $this->error($model->getError());
    }

    // +----------------------------------------------------------------------
    // | 角色
    // +----------------------------------------------------------------------

    /**
     * 角色管理
     * @author 橘子俊 <364666827@qq.com>
     * @return mixed
     */
    public function role()
    {
        $tab_data = $this->tab_data;
        $tab_data['current'] = url('');
        $data_list = RoleModel::field('id,name,intro,ctime,status')->paginate();
        // 分页
        $pages = $data_list->render();
        $this->assign('data_list', $data_list);
        $this->assign('tab_data', $tab_data);
        $this->assign('tab_type', 1);
        $this->assign('pages', $pages);
        return $this->fetch();
    }

    /**
     * 添加角色
     * @author 橘子俊 <364666827@qq.com>
     * @return mixed
     */
    public function addRole()
    {
        if ($this->request->isPost()) {
            $data = $this->request->post();
            // 验证
            $result = $this->validate($data, 'AdminRole');
            if($result !== true) {
                return $this->error($result);
            }
            unset($data['id']);
            if (!RoleModel::create($data)) {
                return $this->error('添加失败！');
            }
            return $this->success('添加成功。');
        }
        $tab_data = [];
        $tab_data['menu'] = [
            ['title' => '添加角色'],
            ['title' => '设置权限'],
        ];
        $this->assign('menu_list', MenuModel::getAllChild());
        $this->assign('tab_data', $tab_data);
        $this->assign('tab_type', 2);
        return $this->fetch('roleform');
    }

    /**
     * 修改角色
     * @author 橘子俊 <364666827@qq.com>
     * @return mixed
     */
    public function editRole($id = 0)
    {
        if ($id <= 1) {
            return $this->error('禁止编辑');
        }

        if ($this->request->isPost()) {
            $data = $this->request->post();
            // 当前登陆用户不可更改自己的分组角色
            if (ADMIN_ROLE == $data['id']) {
                return $this->error('禁止修改当前角色(原因：您不是超级管理员)！');
            }

            // 验证
            $result = $this->validate($data, 'AdminRole');
            if($result !== true) {
                return $this->error($result);
            }
            if (!RoleModel::update($data)) {
                return $this->error('修改失败！');
            }

            // 更新权限缓存
            cache('role_auth_'.$data['id'], $data['auth']);

            return $this->success('修改成功。');
        }
        $tab_data = [];
        $tab_data['menu'] = [
            ['title' => '修改角色'],
            ['title' => '设置权限'],
        ];
        $row = RoleModel::where('id', $id)->field('id,name,intro,auth,status')->find()->toArray();
        $row['auth'] = json_decode($row['auth']);
        $this->assign('data_info', $row);
        $this->assign('menu_list', MenuModel::getAllChild());
        $this->assign('tab_data', $tab_data);
        $this->assign('tab_type', 2);
        return $this->fetch('roleform');
    }
    /**
     * 删除角色
     * @param int $id
     * @author 橘子俊 <364666827@qq.com>
     * @return mixed
     */
    public function delRole()
    {
        $ids   = input('param.ids/a');
        $model = new RoleModel();
        if ($model->del($ids)) {
            return $this->success('删除成功。');
        }
        return $this->error($model->getError());
    }
}
