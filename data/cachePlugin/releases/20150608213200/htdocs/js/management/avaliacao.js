/* Javascript OO Module Pattern */
var avaliacaoModule = (function () {
    return {
        radios: {
            fields: $('input.radio'),
            labels: $('label.radio'),
            toggle: function (element, event) {
                element._ = (element._ || {});
                element._.dom = (element._.dom || $(element));
                element._.checked = (element._.dom.is(':checked') || false);
                element._.disabled = (element._.dom.is(':disabled') || false);
                element._.labels = (element._.labels || this.labels.filter(function () {
                    this._ = (this._ || {});
                    this._.dom = (this._.dom || $(this));
                    this._.field = (this._.field || $(['#', $(this).attr('for')].join('')));
                    this._.checked = (this._.field.is(':checked') || false);
                    this._.disabled = (this._.field.is(':disabled') || false);
                    if (event.type === 'build') {
                        this._.dom[(this._.disabled ? 'addClass' : 'removeClass')]('disabled');
                        this._.dom[(this._.checked ? 'addClass' : 'removeClass')]('checked');
                    }
                    return ($.trim(this._.field.attr('name')).toLowerCase() === $.trim(element._.dom.attr('name')).toLowerCase());
                }));
                element._.label = (element._.label || element._.labels.filter(function () {
                    return ($.trim($(this).attr('for')).toLowerCase() === $.trim(element._.dom.attr('id')).toLowerCase());
                }));
                if (event.type !== 'build') {
                    element._.labels.not(element._.label).removeClass('checked');
                    element._.label[(element._.checked ? 'addClass' : 'removeClass')]('checked');
                }
                return this;
            },
            bind: function () {
                this.fields.on(
                    'change build',
                    function (event) {
                        avaliacaoModule.radios.toggle(this, event);
                    }
                ).trigger('build');
                return this;
            },
            init: function () {
                return this.bind();
            }
        },
        numbers: {
            fields: $('input.number'),
            controls: $('ul.control.number a'),
            keys: {
                aliases: {
                    96: 48,             // 0
                    97: 49,             // 1
                    98: 50,             // 2
                    99: 51,             // 3
                    100: 52,            // 4
                    101: 53,            // 5
                    102: 54,            // 6
                    103: 55,            // 7
                    104: 56,            // 8
                    105: 57             // 9
                },
                functions: {
                    8: '',              // backspace
                    9: '',              // tab
                    37: '',             // left
                    38: 'increase',     // up
                    39: '',             // right
                    40: 'decrease'      // down
                }
            },
            control: function (element, event) {
                event.preventDefault();
                element._ = (element._ || {});
                element._.dom = (element._.dom || $(element));
                element._.action = (element._.action || element._.dom.data('action'));
                element._.field = (element._.field || $(['#', element._.dom.data('field')].join('')));
                if (this.hasOwnProperty(element._.action)) {
                    if (typeof this[element._.action] === 'function') {
                        this[element._.action](element._.field);
                    }
                }
                return this;
            },
            increase: function (element) {
                element._ = (element._ || {});
                element._.dom = (element._.dom || $(element));
                element._.value = ((parseInt(element._.dom.val(), 10) || 0) + 1);
                element._.dom.val(element._.value);
                return this;
            },
            decrease: function (element) {
                element._ = (element._ || {});
                element._.dom = (element._.dom || $(element));
                element._.value = ((parseInt(element._.dom.val(), 10) || 0) - 1);
                element._.dom.val((element._.value >= 0) ? element._.value : 0);
                return this;
            },
            restrict: function (element, event) {
                event = (event || window.event);
                element._ = (element._ || {});
                element._.dom = (element._.dom || $(element));
                element._.charCode = parseInt(((typeof event.which === 'undefined') ? event.keyCode : event.which), 10);
                element._.charCode = (this.keys.aliases.hasOwnProperty(element._.charCode) ? this.keys.aliases[element._.charCode] : element._.charCode);
                element._.charStr = String.fromCharCode(element._.charCode);
                switch (true) {
                    case this.keys.functions.hasOwnProperty(element._.charCode):
                        if (typeof avaliacaoModule.numbers[this.keys.functions[element._.charCode]] === 'function') {
                            avaliacaoModule.numbers[this.keys.functions[element._.charCode]](element._.dom);
                        }
                        break;
                    case !(/\d/.test(element._.charStr)):
                        event.preventDefault();
                        break;
                }
                element._.dom.val(($.trim(element._.dom.val()) !== '') ? parseInt(element._.dom.val(), 10) : ((event.type === 'keydown') ? '' : 0));
            },
            bind: function () {
                this.fields.on(
                    'focus blur keydown',
                    function (event) {
                        avaliacaoModule.numbers.restrict(this, event);
                    }
                );
                this.controls.on(
                    'click',
                    function (event) {
                        avaliacaoModule.numbers.control(this, event);
                    }
                );
                return this;
            },
            init: function () {
                return this.bind();
            }
        },
        layout: {
            macros: $('li.macro'),
            micros: $('li.micro'),
            scores: $('span.score').find('i.total'),
            questions: $('li.question'),
            togglers: $('a.toggle'),
            update: function (element) {
                element._ = (element._ || {});
                element._.dom = (element._.dom || $(element));
                element._.score = (element._.score || {});
                element._.score.dom = (element._.score.dom || element._.dom.find(avaliacaoModule.layout.scores).first());
                element._.score.total = 0;
                element._.micros = (element._.micros || element._.dom.find(this.micros));
                element._.micros.each(function (index, micro) {
                    micro._ = (micro._ || {});
                    micro._.dom = (micro._.dom || $(micro));
                    micro._.score = (micro._.score || {});
                    micro._.score.dom = (micro._.score.dom || micro._.dom.find(avaliacaoModule.layout.scores).first());
                    micro._.score.total = 0;
                    micro._.options = (micro._.options || micro._.dom.find(avaliacaoModule.options.items.selector));
                    micro._.options.filter(':checked').each(function (index, option) {
                        option._ = (option._ || {});
                        option._.dom = (option._.dom || $(option));
                        option._.question = (option._.question || {});
                        option._.question.dom = (option._.question.dom || option._.dom.parents(avaliacaoModule.layout.questions.selector));
                        option._.question.weight = (option._.question.weight || option._.question.dom.data('weight'));
                        option._.question.percentage = (option._.question.percentage || option._.dom.data('percentage'));
                        option._.question.score = ((option._.question.weight * option._.question.percentage) / 100);
                        micro._.score.total = (micro._.score.total + option._.question.score);
                    });
                    element._.score.total = (element._.score.total + micro._.score.total);
                    micro._.score.dom.html(micro._.score.total);
                });
                element._.score.dom.html(element._.score.total);
                return this;
            },
            toggle: function (element, event) {
                event.preventDefault();
                element._ = (element._ || {});
                element._.dom = (element._.dom || $(element));
                element._.parent = (element._.parent || element._.dom.parent().parent()).toggleClass('closed');
                return this;
            },
            bind: function () {
                this.togglers.on(
                    'click',
                    function (event) {
                        avaliacaoModule.layout.toggle(this, event);
                    }
                )
                return this;
            },
            init: function () {
                return this.bind();
            }
        },
        options: {
            items: $('input.option'),
            select: function (element, event) {
                element._ = (element._ || {});
                element._.dom = (element._.dom || $(element));
                element._.parent = (element._.parent || $(element).parents(avaliacaoModule.layout.macros.selector));
                avaliacaoModule.layout.update(element._.parent);
                return this;
            },
            bind: function () {
                this.items.on(
                    'change build',
                    function (event) {
                        avaliacaoModule.options.select(this, event);
                    }
                ).trigger('build');
                return this;
            },
            init: function () {
                return this.bind();
            }
        },
        init: function() {
            var $this = this;
            $this.radios.init();
            $this.numbers.init();
            $this.layout.init();
            $this.options.init();
            return;
        }
    }

}());

$.fn.ready(function() {
    try {

        avaliacaoModule.init();
        $('li.macro').find('.toggle:eq(0)').trigger('click'); 
        $('a.help').on('click', function(){ return false; })
        $('ol.answers').on('change', 'input.option[type=radio]', function(){
            var $this = $(this);
            var $linhasArea = $this.closest('li').find('div.answer-report-mentions');
            /* se for 'não evidenciado', desabilida linhas */
            if ($this.val() === 'D') {
                $linhasArea.css('opacity', 0).find('input').val('0').prop('readonly', true);
                $linhasArea.find('a').hide();
            } else {
                $linhasArea.css('opacity', 1).find('input').prop('readonly', false);
                $linhasArea.find('a').show();
            }
        });
        $('ol.answers').find('input.option[type=radio][value=D]:checked').trigger('change')
        $('div.complement button').on('click', function(){
            $('#overlay1').show();
            $.prettyLoader.show();
        });

    } catch(e) {
        throw e;
    }
});