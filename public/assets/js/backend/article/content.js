define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'article/content/index',
                    add_url: 'article/content/add',
                    edit_url: 'article/content/edit',
                    del_url: 'article/content/del',
                    multi_url: 'article/content/multi',
                    table: 'article_content',
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
                        {field: 'id', title: __('Id'), operate: false},
                        {
                            field: 'title',
                            title: __('Title'),
                            formatter: function (value, row, index) {
                                return '<div style="width: 250px;white-space: pre-wrap;">' + value + '</div>';
                            },
                            operate: 'like'
                        },
                        {field: 'category.name', title: __('Category.name'), operate: false},
                        {
                            field: 'article_category_id',
                            title: __('Category.name'),
                            extend: 'data-source="article/category/index"',
                            addclass: 'selectpage',
                            visible: false
                        },
                        {field: 'images', title: __('Images'), formatter: Table.api.formatter.images, operate: false},

                        {field: 'avatar', title: __('Avatar'), formatter: Table.api.formatter.image, operate: false},
                        {field: 'author', title: __('Author'), operate: false},
                        {field: 'readnum', title: __('Readnum'), operate: false},
                        {
                            field: 'status',
                            title: __('Status'),
                            searchList: {"1": __('Status 1'), "2": __('Status 2')},
                            formatter: Table.api.formatter.status
                        },
                        {
                            field: 'createtime',
                            title: __('Createtime'),
                            operate: 'RANGE',
                            addclass: 'datetimerange',
                            formatter: Table.api.formatter.datetime
                        },
                        {
                            field: 'updatetime',
                            title: __('Updatetime'),
                            operate: 'RANGE',
                            addclass: 'datetimerange',
                            formatter: Table.api.formatter.datetime
                        },
                        {
                            field: 'operate',
                            title: __('Operate'),
                            table: table,
                            events: Table.api.events.operate,
                            formatter: Table.api.formatter.operate,
                            operate: false
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
    return Controller;
});