define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'wechatapp/user/index',
                    add_url: 'wechatapp/user/add',
                    edit_url: 'wechatapp/user/edit',
                    del_url: 'wechatapp/user/del',
                    multi_url: 'wechatapp/user/multi',
                    table: 'wechatapp_user',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                sortOrder: 'desc',
                //可以控制是否默认显示搜索单表,false则隐藏,默认为false
                searchFormVisible: true,
                showSearch: false,
                search: false,
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id'), operate: false},
                        {field: 'nickname', title: __('Nickname'), operate: 'LIKE'},
                        {
                            field: 'headimgurl',
                            title: __('Headimgurl'),
                            formatter: Table.api.formatter.image,
                            operate: false
                        },
                        {
                            field: 'sex',
                            title: __('Sex'),
                            searchList: {"0": __('Sex 0'), "1": __('Sex 1'), "2": __('Sex 2')},
                            formatter: Table.api.formatter.normal,
                            operate: false
                        },
                        {field: 'realname', title: '真实姓名', operate: 'like'},
                        {field: 'phone', title: '联系电话', operate: 'like'},
                        {field: 'address', title: '联系地址', operate: 'like'},
                        {
                            field: 'updatetime',
                            title: __('Updatetime'),
                            operate: 'RANGE',
                            addclass: 'datetimerange',
                            formatter: Table.api.formatter.datetime,
                            operate: false
                        },
                        {
                            field: 'buttons',
                            title: '操作',
                            operate: false,
                            table: table,
                            buttons: [],
                            events: Table.api.events.operate,
                            formatter: function (value, row, index) {
                                var that = $.extend({}, this);
                                var table = $(that.table).clone(true);
                                // 操作配置
                                var options = table ? table.bootstrapTable('getOptions') : {};
                                // 默认按钮组
                                var buttons = [];

                                //报名详情
                                buttons.push(
                                    {
                                        name: 'sign-detail',
                                        title: '收听记录',
                                        text: '收听记录',
                                        classname: 'btn btn-xs btn-success btn-dialog',
                                        url: 'video/log//index?openid='+row.openid,
                                    }
                                );

                                //有bug，要在后面进行赋值一下，不在formatter里写就不用
                                this.buttons = buttons;

                                that.table = table;

                                return Table.api.buttonlink(that, buttons, value, row, index, 'buttons');
                            },
                        },
                    ]
                ]
            });


            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        add: function () {
            Controller.api.bindevent();
        },
        edit: function () {
            Controller.api.bindevent();
        },
        scorelog: function () {
            var userid = $("#userid").val();
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'wechatapp/user/scorelog?userid='+userid ,
                    table: 'score_log',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'scorelog.id',
                columns: [
                    [
                        {field: 'user.nickname', title: '用户', operate: 'like'},
                        {field: 'scorelist.name', title: '积分类型', operate: 'like'},
                        {field: 'name', title: '兑换商品', operate: false},
                        {field: 'score', title: '积分', operate: false},
                        {
                            field: 'createtime',
                            title: '创建时间',
                            operate: 'RANGE',
                            addclass: 'datetimerange',
                            formatter: Table.api.formatter.datetime
                        }
                    ]
                ]
            });


            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            }
        }
    };
    return Controller;
});