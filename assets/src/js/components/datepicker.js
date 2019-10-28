'use strict';

import m from 'mithril';
import Pikaday from 'pikaday';
import 'pikaday/css/pikaday.css';
const now = new Date();

function Component() {
    let startDate, endDate;

    return {
        oncreate: (vnode) => {
            const updateStartDate = function() {
                startPicker.setStartRange(startDate);
                endPicker.setStartRange(startDate);
                endPicker.setMinDate(startDate);
            };
            const updateEndDate = function() {
                startPicker.setEndRange(endDate);
                startPicker.setMaxDate(endDate);
                endPicker.setEndRange(endDate);
            };

            const startPicker = new Pikaday({
                field: document.getElementById('start-date-input'),
                maxDate: new Date(now.getFullYear(), 11, 31),
                onSelect: function() {
                    startDate = this.getDate();
                    updateStartDate();
                }
            });

            const endPicker = new Pikaday({
                field: document.getElementById('end-date-input'),
                maxDate: new Date(now.getFullYear(), 11, 31),
                onSelect: function() {
                    endDate = this.getDate();
                    updateEndDate();
                }
            });

        },
        view: () => (
            <div>
                <input type="text" id="start-date-input" />
                <div id="start-date-container"></div>

                <input type="text" id="end-date-input" />
                <div id="end-date-container"></div>
            </div>
        )
    }
}

export default Component;