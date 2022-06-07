import i18n from 'i18n';
import React, { Component } from 'react';
import fieldHolder from 'components/FieldHolder/FieldHolder';
import Chart from "react-apexcharts";
import Skeleton from 'react-loading-skeleton'

class AnalyticsHitsField extends Component {
    constructor(props) {
        super(props);

        let populatedChartData = this.populateChartData(props.chartData);

        this.state = {
            ...populatedChartData,
            daysShown: 30,
            filteredHits: this.filterSeries(populatedChartData.hitsSeries, 30),
            filteredUnique: this.filterSeries(populatedChartData.uniqueSeries, 30)
        };

        // Bind this to all functions because React
        // ...
        this.handleDaysShownChange = this.handleDaysShownChange.bind(this);
    }

    getChartOptions() {
        return {
            chart: {
                id: 'area-datetime',
                type: 'area',
                zoom: {
                    autoScaleYaxis: true
                }
            },
            dataLabels: {
                enabled: false
            },
            markers: {
                size: 0,
                style: 'hollow',
            },
            xaxis: {
                type: 'datetime',
                tickAmount: 1,
            },
            tooltip: {
                x: {
                    format: 'dd MMM yyyy'
                }
            }
        };
    }

    fillDays(days, from, to) {
        let filled = { ...days };
        let currentDay = new Date(from);
        let maxDay = new Date(to);
        while (currentDay.getTime() < maxDay.getTime()) {
            let key = `${currentDay.getFullYear().toString().padStart(2, '0')}-${(currentDay.getMonth() + 1).toString().padStart(2, '0')}-${currentDay.getDate().toString().padStart(2, '0')}`;
            if (filled[key] === undefined) {
                filled[key] = 0;
            }
            currentDay.setDate(currentDay.getDate() + 1);
        }

        return filled;
    }

    populateChartData(data) {
        const filledHits = this.fillDays(data['Hits']['Days'], data['Hits']['Start'], data['Hits']['End']);
        const filledUnique = this.fillDays(data['Unique']['Days'], data['Unique']['Start'], data['Unique']['End']);

        return {
            hitsSeries: Object.entries(filledHits).sort((a, b) => a[0] < b[0]),
            uniqueSeries: Object.entries(filledUnique).sort((a, b) => a[0] < b[0])
        }
    }

    filterSeries(series, days) {
        if(days == 0) {
            return series;
        }

        let minDate = new Date();
        minDate.setDate(minDate.getDate() - days);
        let minDateKey = `${minDate.getFullYear().toString().padStart(2, '0')}-${(minDate.getMonth() + 1).toString().padStart(2, '0')}-${minDate.getDate().toString().padStart(2, '0')}`;

        return series.filter((s) => s[0] >= minDateKey);
    }

    handleDaysShownChange(event) {
        this.setState({
            daysShown: event.currentTarget.value,
            filteredHits: this.filterSeries(this.state.hitsSeries, event.currentTarget.value),
            filteredUnique: this.filterSeries(this.state.uniqueSeries, event.currentTarget.value)
        });
    }

    render() {
        return (
            <div>
                <div class="analytics-field__box">
                    <div class="days-shown-box">
                        <div class="days-shown-box__wrapper">
                            {
                                [[7, "7D"], [30, "1M"], [90, "3M"], [365, "1Y"], [0, "All"]].map((entry) => {
                                    return (
                                        <div className={"days-shown-box__field " + (this.state.daysShown == entry[0] ? "active" : "") }>
                                            <input type="radio" id={"daysshown-" + entry[0]} name="daysshown" value={entry[0]} onChange={this.handleDaysShownChange} />
                                            <label for={"daysshown-" + entry[0]}>{entry[1]}</label>
                                        </div>
                                    )
                                })
                            }
                        </div>
                    </div>
                    { !this.state.hitsSeries || !this.state.uniqueSeries && <Skeleton count={10} /> }
                    { !!this.state.hitsSeries && !!this.state.uniqueSeries && <Chart
                        options={this.getChartOptions()}
                        series={[{
                            name: i18n._t('Analytics.DailyHits', 'Daily hits'),
                            data: this.state.filteredHits
                        }, {
                            name: i18n._t('Analytics.UniqueHits', 'Unique hits'),
                            data: this.state.filteredUnique
                        }]}
                        width="500"
                        height="400"
                    /> }
                </div>
            </div>
        )
    }
}

export default fieldHolder(AnalyticsHitsField);