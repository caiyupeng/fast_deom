define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'weixin/config/index',
                    add_url: 'weixin/config/add',
                    edit_url: 'weixin/config/edit',
                    del_url: 'weixin/config/del',
                    multi_url: 'weixin/config/multi',
                    table: 'weixin_config',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                columns: [
                    [
                        {field: 'name', title: __('Name')},
                        {field: 'appid', title: __('Appid')},
                        {field: 'appsecret', title: __('Appsecret')},
                        {field: 'token', title: __('Token')},
                        {field: 'accesstoken', title: __('Accesstoken')},
                        {
                            field: 'updatetime',
                            title: __('Updatetime'),
                            operate: 'RANGE',
                            addclass: 'datetimerange',
                            formatter: Table.api.formatter.datetime
                        },
                        {field: 'encodingaeskey', title: __('Encodingaeskey')},
                        {
                            field: 'status',
                            title: __('Status'),
                            visible: false,
                            searchList: {"1": __('Status 1'), "2": __('Status 2')}
                        },
                        {field: 'status_text', title: __('Status'), operate: false},
                        {
                            field: 'operate',
                            title: __('Operate'),
                            table: table,
                            events: Table.api.events.operate,
                            formatter: Table.api.formatter.operate
                        }
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
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            }
        }
    };

    $("#updateconfig").click(function () {
        Layer.confirm(
            '确定更新吗',
            {icon: 3, title: __('Warning'), offset: 0, shadeClose: true},
            function (index) {
                $.post('weixin/config/updateconfig', {}, function (data) {
                    console.log(data);
                });
                $("#table").bootstrapTable('refresh');
                // Table.api.multi("del", ids, table, that);
                Layer.close(index);
            }
        );
    });
    return Controller;
});