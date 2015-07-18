function StatsFC_Form(key) {
    this.domain      = 'https://api.statsfc.com';
    this.referer     = '';
    this.key         = key;
    this.competition = 'EPL';
    this.date        = '';
    this.limit       = 0;
    this.highlight   = '';
    this.showBadges  = false;
    this.showScore   = false;

    var $j = jQuery;

    this.display = function(placeholder) {
        if (placeholder.length == 0) {
            return;
        }

        var $placeholder = $j('#' + placeholder);

        if ($placeholder.length == 0) {
            return;
        }

        if (this.referer.length == 0) {
            this.referer = window.location.hostname;
        }

        var $container = $j('<div>').addClass('sfc_form');

        // Store globals variables here so we can use it later.
        var domain     = this.domain;
        var highlight  = this.highlight;
        var showBadges = (this.showBadges === true || this.showBadges === 'true');
        var showScore  = (this.showScore === true || this.showScore === 'true');

        $j.getJSON(
            domain + '/crowdscores/form.php?callback=?',
            {
                key:         this.key,
                domain:      this.referer,
                competition: this.competition,
                date:        this.date,
                limit:       this.limit,
                badges:      this.showBadges,
                showScore:   this.showScore
            },
            function(data) {
                if (data.error) {
                    $container.append(
                        $j('<p>').css('text-align', 'center').append(
                            $j('<a>').attr({ href: 'https://statsfc.com', title: 'Football widgets and API', target: '_blank' }).text('StatsFC.com'),
                            ' – ',
                            data.error
                        )
                    );

                    return;
                }

                var $table = $j('<table>');
                var $thead = $j('<thead>');
                var $tbody = $j('<tbody>');

                var $team = $j('<th>').text('Team');
                if (showBadges) {
                    $team.addClass('sfc_team');
                }

                $thead.append(
                    $j('<tr>').append(
                        $j('<th>'),
                        $team,
                        $j('<th>').text('Form')
                    )
                );

                if (data.form.length > 0) {
                    $j.each(data.form, function(key, val) {
                        var $row = $j('<tr>');

                        if (highlight == val.team) {
                            $row.addClass('sfc_highlight');
                        }

                        var $team     = $j('<td>').addClass('sfc_team sfc_badge_' + val.path).text(val.team);
                        var $formData = $j('<td>').addClass('sfc_form');

                        if (showBadges) {
                            $team.css('background-image', 'url(https://api.statsfc.com/kit/' + val.path + '.svg)');
                        }

                        $j.each(val.form, function(formKey, formVal) {
                            var $result = $j('<span>').addClass('sfc_' + formVal.result).text(formVal.result);

                            if (showScore) {
                                var $data = $j('<abbr>').attr('title', formVal.score).append($result);
                            } else {
                                var $data = $result;
                            }

                            $formData.append($data);
                        });

                        $row.append(
                            $j('<td>').addClass('sfc_numeric').text(val.pos),
                            $team,
                            $formData
                        );

                        $tbody.append($row);
                    });

                    $table.append($thead, $tbody);
                }

                $container.append($table);

                if (data.customer.attribution) {
                    $container.append(
                        $j('<div>').attr('class', 'sfc_footer').append(
                            $j('<p>').append(
                                $j('<small>').append('Powered by ').append(
                                    $j('<a>').attr({ href: 'https://statsfc.com', title: 'StatsFC – Football widgets', target: '_blank' }).text('StatsFC.com')
                                ).append('. Fan data via ').append(
                                    $j('<a>').attr({ href: 'https://crowdscores.com', title: 'CrowdScores', target: '_blank' }).text('CrowdScores.com')
                                )
                            )
                        )
                    );
                }
            }
        );

        $j('#' + placeholder).append($container);
    };
}
