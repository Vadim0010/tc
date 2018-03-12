(function(){
    var users_group = $('.journal-list'),
        input_attennd = $('input[name=attend-user]'),
        list_listeners = $('table#list-listeners-all-lessons'),
        group = {
            current_lesson: $('#current-lesson').attr('data-lesson'),
            current_group: $('#current-lesson').attr('data-group'),
            current_ha: $('#current-lesson').attr('data-ha')
        },
        data_users_group = [],
        lessons_course = $('div.lessons-course'),
        data_lessons_course = []
    ;

    /*Отметить присутствие учащегося (присутствует)*/
    input_attennd.on('ifChecked', function () {
        var user_id = $(this).parents('tr.journal-list').attr('id'),
            lesson_id = group.current_lesson,
            td = list_listeners.find('td[data-user=' + user_id + '][data-lesson=' + lesson_id + ']');

        td.removeClass('danger').addClass('success')
    });

    /*Отметить присутствие учащегося (отсутствует)*/
    input_attennd.on('ifUnchecked', function () {
        var user_id = $(this).parents('tr.journal-list').attr('id'),
            lesson_id = group.current_lesson,
            td = list_listeners.find('td[data-user=' + user_id + '][data-lesson=' + lesson_id + ']');

        td.removeClass('success').addClass('danger');
    });

    /*Сохранить изменения текущего урока в группе*/
    $('button#save-lesson-group').on('click', function(e) {
        e.preventDefault();
        data_users_group = [];

        // if (DATA_GROUP.group.date_current < DATA_GROUP.group.date_begin) {
        //     $.notify('На текущий момент в данной группе нельзя делать какие-либо изменения', 'info');
        //     return false;
        // }

        if (users_group.length > 0) {
            $.each(users_group, function (key, user) {
                var data = {
                    user_id: $(user).attr('id') ,
                    user_attend: 0,
                    user_mark: $(user).find('span.select2-chosen').text(),
                    user_comment: $(user).find('input[name=comment-user]').val()
                };

                if ($(user).find('div.icheckbox_square-blue').hasClass('checked')) {
                    data.user_attend = 1;
                }

                data_users_group.push(data);
            })
        } else {
            $.notify('В данной группе нет ни одного учащегося!', 'info');
            return false;
        }

        ajaxRequest(
            "POST",
            DATA_GROUP.route_name.lesson_save,
            {data_user: JSON.stringify(data_users_group), data_group: JSON.stringify(group)},
            function () {
                $('button#save-lesson-group').button('loading');
            },
            function (data) {
                $.notify(data, 'success');
                setTimeout(function () {
                    window.location.reload()
                }, 1000);
            },
            function (jqXHR, exception) {
                var message = getErrorsMessage(jqXHR, exception);
                $.notify(message.msg, message.status);
            },
            function () {
                data_users_group = [];
                $('button#save-lesson-group').button('reset');
            }
        );
    });

    /*Показать данные выбранного урока в группе*/
    $('button.edit-lesson-group').on('click', function (e) {
        e.preventDefault();
        var lesson_id = $(this).attr('data-lesson'),
            lesson_title = $('#edit-lesson-current-group-title-content');

        $('#edit-lesson-current-group-block').hide();
        $('#edit-lesson-current-group-list-users').empty();

        ajaxRequest(
            "PUT",
            DATA_GROUP.route_name.lesson_edit,
            {data: JSON.stringify({group: group.current_group, lesson: lesson_id})},
            function(){
                var indicator = $('#download-indicator-block');

                indicator.append(createDownloadIndicator()).show();

                $('html, body').animate(
                    {scrollTop: indicator.offset().top},
                    500
                );

            },
            function(data){
                var lesson = data.lesson,
                    teacher = data.teacher,
                    users = data.users,
                    list_users = $('#edit-lesson-current-group-list-users')
                ;

                $('#edit-lesson-current-group-block').show();

                if (lesson.id) {
                    group.current_lesson = lesson.id;
                    list_users.attr('data-lesson', lesson.id);
                } else {
                    $.notify('Не удалось найти урок с id: ' + lesson.id, 'error');
                    return false;
                }

                if (lesson.title) {
                    lesson_title.find('span:first-child').text(lesson.title + ' - ' + lesson.createdAt);
                } else {
                    lesson_title.find('span:first-child').text('lesson.createdAt');
                }

                if (teacher.name) {
                    lesson_title.find('span:last-child').text(teacher.name);
                }

                if (users) {

                    $.each(users, function (key, user) {
                        var option_mark = [$('<option>', {'value': ''})];

                        for (var i = 0; i <= 10; i++) {
                            var select_option = $('<option>', {'value': i}).text(i);

                            if (i === user.mark) {
                                select_option.prop('selected', true);
                            }

                            option_mark.push(select_option);
                        }

                        list_users.append($('<tr>', {'class': 'edit-lesson-current-group-user', 'data-user': user.id})
                            .append([
                                $('<th>', {'class': 'edit-lesson-current-group-user-name'}).text(user.name),
                                $('<td>', {'class': 'edit-lesson-current-group-user-attend'}).append(
                                    $('<label>', {'for': 'check'}).append(
                                        $('<input>', {'type': 'checkbox', 'name': 'attend-user'}).prop('checked', user.isAttend)
                                    )
                                ),
                                $('<td>', {'class': 'edit-lesson-current-group-user-mark'}).append(
                                    $('<label>', {'for': 'mark'}).append(
                                        $('<select>', {'type': 'number', 'name': 'mark-user'}).append(option_mark)
                                    )
                                ),
                                $('<td>', {'class': 'edit-lesson-current-group-user-comment'}).append(
                                    $('<label>', {'for':'comment', 'class':'form-control-label'}).append(
                                        $('<input>', {'type': 'text', 'class': 'form-horizontal', 'name': 'comment-user'}).val(user.comment)
                                    )
                                )
                            ])
                        );
                    });
                }
            },
            function(jqXHR, exception){
                var message = getErrorsMessage(jqXHR, exception);
                $.notify(message.msg, message.status);
            },
            function(){
                var list = $('#edit-lesson-current-group-block');

                $('#download-indicator-block').hide();
                removeDownloadIndicator();

                $('html, body').animate(
                    {scrollTop: list.offset().top},
                    500
                );
            }
        );

    });

    /*Сохранить новые данные выбранного урока*/
    $('button#update-lesson-group').on('click', function(e) {
        e.preventDefault();
        var list_users = $('.edit-lesson-current-group-user');
        data_users_group = [];

        if (list_users.length > 0) {
            $.each(list_users, function (key, user) {
                var data = {
                    user_id: $(user).attr('data-user') ,
                    user_attend: 0,
                    user_mark: $(user).find('select').val(),
                    user_comment: $(user).find('input[name=comment-user]').val()
                };

                if ($(user).find('input[name=attend-user]').is(':checked')) {
                    data.user_attend = 1;
                }

                data_users_group.push(data);
            })
        } else {
            $.notify('В данной группе нет ни одного учащегося!', 'info');
            return false;
        }

        ajaxRequest(
            "POST",
            DATA_GROUP.route_name.lesson_save,
            {data_user: JSON.stringify(data_users_group), data_group: JSON.stringify(group)},
            function () {
                $('button#update-lesson-group').button('loading');
            },
            function (data) {
                $.notify(data, 'success');
                setTimeout(function () {
                    window.location.reload()
                }, 1000);
            },
            function (jqXHR, exception) {
                var message = getErrorsMessage(jqXHR, exception);
                $.notify(message.msg, message.status);
            },
            function () {
                data_users_group = [];
                $('button#update-lesson-group').button('reset');
            }
        );
    });

    $('#edit-lesson-current-group-list-users').on('click', 'td', function (e) {
        var user_id = $(this).parents('tr.edit-lesson-current-group-user').attr('data-user'),
            lesson_id = group.current_lesson,
            mark = $(this).find('select[name=mark-user]').val(),
            td = list_listeners.find('td[data-user=' + user_id + '][data-lesson=' + lesson_id + ']');

        if ($(this).hasClass('edit-lesson-current-group-user-attend')) {
            if ($(this).find('input[name=attend-user]').prop('checked')) {
                td.removeClass('danger').addClass('success');
            } else {
                td.removeClass('success').addClass('danger');
            }
        } else if ($(this).hasClass('edit-lesson-current-group-user-mark')) {
            td.text(mark)
        }
    });

    /*Добавить домашнее задание*/
    $('button#add-home-assignment-group').on('click', function(e) {
        e.preventDefault();
        var haDropzone = Dropzone.forElement("#add-home-assignment-dropzone");
        haDropzone.options.params = {
            group_id: group.current_group,
            lesson_id: group.current_lesson,
            ha_id: group.current_ha,
            ha_title: $('input[id=home-assignment-title]').val(),
            ha_body: $('textarea[id=home-assignment-body]').val(),
            ha_selected: $('#selected-home-assignment').val()
        };

        if (haDropzone.getQueuedFiles().length > 0) {
            haDropzone.processQueue();
        } else {
            ajaxRequest(
                "POST",
                DATA_GROUP.route_name.home_assignment_add,
                haDropzone.options.params,
                function () {
                    $('button#add-home-assignment-group').button('loading');
                },
                function (response) {
                    $.notify(response, 'success');
                    setTimeout( function() {
                        window.location.reload();
                    }, 1000);
                },
                function (jqXHR, exception) {
                    var message = getErrorsMessage(jqXHR, exception);
                    $.notify(message.msg, message.status);
                },
                function () {
                    $('button#add-home-assignment-group').button('reset');
                }
            );
        }

        haDropzone.on("successmultiple", function(files, response) {
            haDropzone.removeAllFiles(files);
            $.notify(response, 'success');
            setTimeout( function() {
                window.location.reload();
            }, 1000);
        });

        haDropzone.on("errormultiple", function(files, response) {
            $.notify(response, 'error');
        });

    });

    /*Показать домашнее задание пользователю*/
    $('table#current-listener-lessons-list').on('click', 'td', function (e) {
        e.stopPropagation();
        var lesson = $(this).attr('data-lesson'),
            result  = $('div#home-assignment-result');
        result.empty();
        ajaxRequest(
            "POST",
            DATA_GROUP_LISTENER.route_name.ha_show,
            {data: JSON.stringify({lesson: lesson, group: DATA_GROUP_LISTENER.data.group})},
            function () {
                result.append(createDownloadIndicator());
                $('html, body').animate(
                    {scrollTop: result.offset().top},
                    500
                );
            },
            function (data) {
                if ( typeof(data) === 'object' ) {
                    var title = data.title ? data.title : 'Домашнее задание',
                        body = data.body ? data.body : 'Для данного занятия нет дополнительной информации',
                        comment = data.comment ? data.comment : false,
                        path = $('<ul>', {'class': 'list-group'});

                    if (data.files.length > 0) {
                        data.files.forEach( function (file) {
                            path
                                .append(
                                    $('<li>', {'class': 'list-group-item'}).append(
                                        $('<a>', {
                                            'class': 'file-name-ha',
                                            'target': '_blank',
                                            'href': '/download/' + file.id,
                                            'title': file.fileName
                                        }).text(file.fileName)
                                    )
                                )
                            ;
                        });
                    } else {
                        path = $('<p>').text('Для данного занятия нет дополнительного материала');
                    }

                    if(comment) {
                        var note = $('<div>', {'class': 'row'}).append(
                            $('<div>', {'class': 'col-md-12 col-lg-12'}).append(
                                $('<div>', {'class': 'alert alert-danger', 'role': 'alert'}).text(comment)
                            )
                        );
                    } else {
                        note = '';
                    }

                    result.append(
                        $('<div>', {'class': 'panel panel-primary'}).append([
                            $('<div>', {'class': 'panel panel-heading'}).append($('<h4>').text(title)),
                            $('<div>', {'class': 'panel panel-body'}).append(
                                $('<div>', {'class': 'row'}).append([
                                    $('<div>', {'class': 'col-md-8 col-lg-6'}).append([
                                        $('<div>', {'class': 'alert alert-success', 'role': 'alert'}).text('Список заданий'),
                                        $('<p>').text(body)
                                    ]),
                                    $('<div>', {'class': 'col-md-4 col-lg-6'}).append([
                                        $('<div>', {'class': 'alert alert-success', 'role': 'alert'}).text('Материал для занятия'),
                                        path
                                    ])
                                ]),
                                note
                            )
                        ])
                    );

                    $('html, body').animate(
                        {scrollTop: result.offset().top},
                        500
                    );
                } else if (typeof (data) === 'string') {
                    result.append($('<div>', {'class': 'alert alert-info', 'role': 'alert'}).text(data));
                } else {
                    result.append($('<div>', {'class': 'alert alert-info', 'role': 'alert'}).text('Не удалось получить данные'));
                }
            },
            function (jqXHR, exception) {
                var message = getErrorsMessage(jqXHR, exception);
                $.notify(message.msg, message.status);
            },
            function () {
                removeDownloadIndicator();
            }
        );
    });

    /*Редактировать занятия*/
    $('button#edit-lessons-course').on('click', function (e) {
        e.preventDefault();
        data_lessons_course = [];

        $.each(lessons_course, function(key, lesson) {
            var lesson_id = $(lesson).attr('id'),
                lesson_title = $(lesson).find('input[name=title]').val(),
                lesson_body = $(lesson).find('textarea[name=body]').val();

            if ($(lesson).find('div.form-group').hasClass('has-error')) {
                $(lesson).find('div.form-group').removeClass('has-error')
            }

            data_lessons_course.push(
                {
                    lesson_id: lesson_id,
                    lesson_title: lesson_title,
                    lesson_body: lesson_body
                }
            );

            $('div.lessons-course[id=' + lesson_id + ']').find('span.text-lesson-title').text(lesson_title);
        });

        ajaxRequest(
            "PUT",
            COURSE_LESSONS_DATA.route_name.edit_lessons,
            {data_lessons: JSON.stringify(data_lessons_course), data_course: JSON.stringify(COURSE_LESSONS_DATA.course_data)},
            function(){
                $('button#edit-lessons-course')
                    .notify(
                        'Обработка данных...',
                        {className: 'info', position: "bottom left", autoHide: false}
                    );
                $.notify('Обработка данных...', 'info');
            },
            function(data){

                if (typeof (data) === 'object') {
                    var lesson_error = $('div[id='+ data.id +']');

                    lesson_error.find('div.form-group:first-child').addClass('has-error');
                    $.notify(data.message, data.status);
                    $('html, body').animate(
                        {scrollTop: lesson_error.offset().top - 150},
                        800
                    );

                    $('button#edit-lessons-course').notify();
                } else {
                    $('button#edit-lessons-course')
                        .notify(
                            data,
                            {className: 'success', position: "bottom left"}
                        );
                    $.notify(data, 'success');
                }
            },
            function(jqXHR, exception){
                var message = getErrorsMessage(jqXHR, exception);

                $('button#edit-lessons-course')
                    .notify(
                        message.msg,
                        {className: message.status, position: "bottom left"}
                    );
                $.notify(message.msg, message.status);
            },
            function(){
                data_lessons_course = [];
            }
        );
    });

    /*Добавить курсу новое занятие*/
    $('button#add-lessons-course').on('click', function(e) {
        e.preventDefault();

        ajaxRequest(
            "POST",
            COURSE_LESSONS_DATA.route_name.add_lesson,
            {data_course: JSON.stringify(COURSE_LESSONS_DATA.course_data)},
            function(){
                $('button#add-lessons-course')
                    .notify(
                        'Обработка данных...',
                        {className: 'info', position: "bottom left", autoHide: false}
                    );
                $.notify('Обработка данных...', 'info');
            },
            function(data){
                var lessons_block = $('div#list-lessons-block'),
                    new_lesson = $('<div>', {'class': 'col-md-6 col-lg-6'})
                        .append($('<div>', {'class': 'panel panel-info lessons-course', 'id': data.lesson_id})
                            .append([
                                $('<div>', {'class': 'panel-heading lesson-title'}).append([
                                    $('<span>', {'class': 'text-lesson-title'}).text(data.lesson_title),
                                    $('<button>', {'class': 'btn btn-danger btn-sm btn-lesson-delete', 'data-lesson': data.lesson_id}).text('Удалить')
                                ]),
                                $('<div>', {'class': 'panel-body form-horizontal'}).append([
                                    $('<div>', {'class': 'form-group'}).append([
                                        $('<label>', {'for': 'title' + data.lesson_id, 'class': 'col-md-4'}).text('Тема:'),
                                        $('<div>', {'class': 'col-md-8'}).append(
                                            $('<input>', {
                                                'id': 'title' + data.lesson_id,
                                                'type': 'text',
                                                'class': 'form-control',
                                                'name': 'title',
                                                'required': 'required'
                                            }).val(data.lesson_title)
                                        )
                                    ]),
                                    $('<div>', {'class': 'form-group'}).append([
                                        $('<label>', {'for': 'body' + data.lesson_id, 'class': 'col-md-4'}).text('Описание:'),
                                        $('<div>', {'class': 'col-md-8'}).append(
                                            $('<textarea>', {
                                                'id': 'body' + data.lesson_id,
                                                'class': 'form-control',
                                                'name': 'body'
                                            })
                                        )
                                    ])
                                ])
                            ])
                        );

                if (data.number_lesson & 1) {
                    lessons_block
                        .append($('<div>', {'class': 'row'})
                            .append(new_lesson)
                        );

                } else {
                    $(lessons_block)
                        .find('div.row:last')
                        .append(new_lesson);

                }

                $('button#add-lessons-course')
                    .notify(
                        data.message,
                        {className: data.status, position: "bottom left"}
                    );
                $.notify(data.message, data.status);
            },
            function(jqXHR, exception){
                var message = getErrorsMessage(jqXHR, exception);

                $('button#add-lessons-course')
                    .notify(
                        message.msg,
                        {className: message.status, position: "bottom left"}
                    );
                $.notify(message.msg, message.status);
            },
            function(){
                lessons_course = $('div.lessons-course');
                data_lessons_course = [];
            }
        );
    });

    /*Удаление занятия у курса*/
    $('div#list-lessons-block').on('click', function(e) {
        var button_del = $(e.target);

        if (button_del.hasClass('btn-lesson-delete')) {
            var lesson_id = button_del.attr('data-lesson');

            ajaxRequest(
                "DELETE",
                COURSE_LESSONS_DATA.route_name.delete_lesson,
                {data: JSON.stringify({data_course: COURSE_LESSONS_DATA.course_data, data_lesson: lesson_id})},
                function(){
                    $.notify('Обработка данных...', 'info');
                },
                function(data){
                    $('div.lessons-course[id=' + lesson_id + ']').remove();
                    $.notify(data, 'success');
                },
                function(jqXHR, exception){
                    var message = getErrorsMessage(jqXHR, exception);
                    $.notify(message.msg, message.status);
                },
                function(){
                    lessons_course = $('div.lessons-course');
                    data_lessons_course = [];
                }
            );
        }
    });

    /*Получить табель учета рабочего временя преподавателя*/
    $('button#get-teacher-info').on('click', function () {
        var teacher_id = $('select#selected-teacher').val(),
            result = $('div#selected-teacher-result-block');

        result.empty();

        if (teacher_id) {
            ajaxRequest(
                "POST",
                DATA_ACCOUNTANT.route_name.get_hours,
                {data: teacher_id},
                function () {
                    $('button#get-teacher-info').button('loading');
                    result.append(createDownloadIndicator());
                    $.notify('Получение данных...', 'info');
                },
                function (data) {
                    var lessons = JSON.parse(data);

                    if (lessons.lessons) {
                        var table = $('<table>', {'class': 'table table-bordered journal-table'}).append(
                            $('<tr>').append([
                                $('<th>', {'class': 'group-field-name text-center'}).text('Месяц'),
                                $('<th>', {'class': 'group-field-name text-center'}).text('Группы (Количество учащихся)'),
                                $('<th>', {'class': 'group-field-name text-center'}).text('Количество часов')
                            ])
                        );

                        $.each(lessons.lessons, function (year, month) {
                            $.each(month, function (m_name, m_data) {
                                var td_month = $('<th>', {'class': 'group-field-name text-center', 'rowspan': Object.keys(m_data.groups).length})
                                        .text(getNameOfMonth(m_name) + ' ' + year);

                                if ($.isEmptyObject(m_data.groups)) {
                                    table.append(
                                        $('<tr>').append([
                                            td_month,
                                            $('<td>', {'class': 'group-field-name'}).text('Группы отсутствуют'),
                                            $('<td>', {'class': 'group-field-name'}).text(0)
                                        ])
                                    );
                                } else {
                                    var counter = 1;
                                    $.each(m_data.groups, function (k, group) {
                                        var title_group = $('<p>').text(group.data.title);

                                        if (group.data.completed) {
                                            title_group.append(
                                                $('<span>', {'class': 'label label-success label-group-complete'}).text('Завершена')
                                            );
                                        }

                                        if (counter === 1) {
                                            table.append(
                                                $('<tr>').append([
                                                    td_month,
                                                    $('<td>', {'class': 'group-field-name'}).html(title_group),
                                                    $('<td>', {'class': 'group-field-name'}).html(group.hours)
                                                ])
                                            );
                                        } else {
                                            table.append(
                                                $('<tr>').append([
                                                    $('<td>', {'class': 'group-field-name'}).html(title_group),
                                                    $('<td>', {'class': 'group-field-name'}).html(group.hours)
                                                ])
                                            );
                                        }
                                        ++counter;
                                    })
                                }

                                table.append(
                                    $('<tr>').append([
                                        $('<th>'),
                                        $('<th>'),
                                        $('<th>', {'class': 'group-field-name group-field-name-left'}).text('Итого за месяц: ' + m_data.hours)
                                    ])
                                );
                            });
                        });

                        result.append(
                            $('<div>', {'class': 'panel-table'}).append(table)
                        );
                    } else {
                        var message = lessons.message ? lessons.message : 'За последний год не проведено ни одного занятия';
                        result.append($('<div>', {'class': 'alert alert-danger'}).text(message));
                    }
                },
                function (jqXHR, exception) {
                    var message = getErrorsMessage(jqXHR, exception);
                    $.notify(message.msg, message.status);
                },
                function () {
                    removeDownloadIndicator();
                    $('button#get-teacher-info').button('reset');
                }
            );
        } else {
            result.append($('<div>', {'class': 'alert alert-danger'}).text('Выберите преподавателя из списка'));
        }
    });

    function ajaxRequest(type, url, data, beforeSend, success, error, complete)
    {
        $.ajax({
            type: type,
            url: url,
            data: data,
            beforeSend: beforeSend,
            success: success,
            error: error,
            complete: complete
        });
    }

    function getErrorsMessage(jqXHR, exception)
    {
        var msg = 'Упс, что-то пошло не так!',
            status = 'error'
        ;

        if (jqXHR.status === 0) {
            msg = 'Отсутствует сеть. Проверьте соединение с интернетом.';
        } else if (jqXHR.status === 400) {
            msg = jqXHR.responseJSON ? jqXHR.responseJSON : 'Упс, что-то пошло не так!';
        } else if (jqXHR.status === 404) {
            msg = 'Запрошенная страница не найдена.';
        } else if (jqXHR.status === 500) {
            msg = jqXHR.responseJSON ? jqXHR.responseJSON : 'Ошибка на сервере';
        } else if (exception === 'parsererror') {
            msg = 'Не удалось выполнить анализ данных.';
        } else if (exception === 'timeout') {
            msg = 'Ошибка времени ожидания.';
        } else if (exception === 'abort') {
            msg = 'Запрос прерван.';
        }

        return {msg: msg, status: status};
    }

    /*Создать индикатора загрузки*/
    function createDownloadIndicator()
    {
        var downloader = $('<div>', {
            'id': 'floatingCirclesG'
        });

        for (var i = 1; i <= 8; i++){
            downloader.append($('<div>', {
                'class': 'f_circleG',
                'id': 'frotateG_0' + i
            }));
        }

        return downloader;
    }

    /*Удалить индикатора загрузки*/
    function removeDownloadIndicator()
    {
        if( $('div').is('#floatingCirclesG')){
            $('#floatingCirclesG').remove();
        }
    }

    function getNameOfMonth(month)
    {
        switch (month) {
            case '01':
                return 'Январь';
            case '02':
                return 'Февраль';
            case '03':
                return 'Март';
            case '04':
                return 'Апрель';
            case '05':
                return 'Май';
            case '06':
                return 'Июнь';
            case '07':
                return 'Июль';
            case '08':
                return 'Август';
            case '09':
                return 'Сентябрь';
            case '10':
                return 'Октябрь';
            case '11':
                return 'Ноябрь';
            case '12':
                return 'Декабрь';
        }
    }

})();