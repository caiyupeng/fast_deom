define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'weixin/member/index',
                    add_url: 'weixin/member/add',
                    edit_url: 'weixin/member/edit',
                    del_url: 'weixin/member/del',
                    multi_url: 'weixin/member/multi',
                    table: 'weixin_member',
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
                        {checkbox: true},
                        {field: 'id', title: __('Id')},
                        {
                            field: 'miniavatar',
                            title: __('Miniavatar'),
                            operate: false,
                            formatter: Controller.api.formatter.miniavatar
                        },
                        {field: 'openid', title: __('Openid')},
                        {field: 'nickname', title: __('Nickname')},
                        {
                            field: 'sex',
                            title: __('Sex'),
                            searchList: {"1": __('Sex 1'), "2": __('Sex 2')},
                            formatter: Table.api.formatter.normal
                        },
                        {
                            field: 'createtime',
                            title: __('Createtime'),
                            operate: 'RANGE',
                            addclass: 'datetimerange',
                            formatter: Table.api.formatter.datetime
                        }
                    ]
                ]
            });
            //Table.api.formatter.images
            // 为表格绑定事件
            Table.api.bindevent(table);
            $(document).on("click", ".view-screenshots", function () {
                var data = [];
                data.push({
                    "src": $(this).attr('src')
                });
                var json = {
                    "title": $(this).attr('title'),
                    "data": data
                };
                top.Layer.photos(top.JSON.parse(JSON.stringify({photos: json})));
            });
        },
        add: function () {
            Controller.api.bindevent();
        },
        edit: function () {
            Controller.api.bindevent();
        },
        info: function () {
            $(document).on("click", ".view-screenshots", function () {
                var data = [];
                data.push({
                    "src": $(this).attr('src')
                });
                var json = {
                    "title": $(this).attr('title'),
                    "data": data
                };
                top.Layer.photos(top.JSON.parse(JSON.stringify({photos: json})));
            });
        },
        api: {
            formatter: {
                miniavatar: function (value, row, index) {
                    var miniavatars = '';
                    if (row.miniavatar) {
                        miniavatars = ' <img src="' + row.miniavatar + '" data-index="1" class="view-screenshots text-success" title="' + row.nickname + '" data-toggle="tooltip" style="width: 50px;">';
                    }
                    return miniavatars;
                },
            },
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            }
        }
    };
    return Controller;
});