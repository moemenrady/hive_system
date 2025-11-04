@extends('layouts.app_page')

@section('content')
    <div class="container py-4">
        <div class="row g-4 justify-content-center">
            <div class="col-12">
                <div class="card p-3 animate__animated animate__fadeInUp">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <button id="prev-month" class="btn btn-sm theme-btn">&lt;</button>
                        <h5 id="calendar-title" class="mb-0 fw-bold"></h5>
                        <button id="next-month" class="btn btn-sm theme-btn">&gt;</button>
                    </div>

                    <div id="calendar" class="border p-2 rounded"></div>

                    <div class="mt-3 text-muted text-center">
                        اضغط على أي يوم لعرض الشيفتات الخاصة به.
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const calendarEl = document.getElementById('calendar');
            const titleEl = document.getElementById('calendar-title');
            let current = new Date();

            const fetchEnabled = false;
            const fetchUrl = `{{ route('bookings.calendar') }}`;
            const weekdayNames = ['السبت', 'الأحد', 'الاثنين', 'الثلاثاء', 'الأربعاء', 'الخميس', 'الجمعة'];

            function renderCalendar(year, month) {
                titleEl.textContent = `${year} / ${month + 1}`;

                let firstDay = (new Date(year, month, 1).getDay() + 1) % 7;
                let daysInMonth = new Date(year, month + 1, 0).getDate();

                const loadData = fetchEnabled ?
                    fetch(`${fetchUrl}?year=${year}&month=${month+1}`).then(res => res.json()) :
                    Promise.resolve({});

                loadData.then(data => {
                    let html = `<table class="table text-center"><tbody><tr>`;
                    let day = 0;

                    for (let i = 0; i < firstDay; i++) {
                        html += `<td></td>`;
                        day++;
                    }

                    for (let d = 1; d <= daysInMonth; d++) {
                        if (day % 7 === 0) html += `</tr><tr>`;

                        let bookings = (data && data[d]) ? data[d] : [];
                        let dots = '';
                        if (bookings.length) {
                            bookings.forEach(b => {
                                dots += `<span class="booking-dot"></span>`;
                            });
                        }

                        let todayClass = "";
                        let now = new Date();
                        if (d === now.getDate() && year === now.getFullYear() && month === now.getMonth()) {
                            todayClass = "today";
                        }

                        let weekdayIndex = (new Date(year, month, d).getDay() + 1) % 7;
                        let weekdayLabel = weekdayNames[weekdayIndex];

                        html += `<td class="${todayClass}" data-date="${year}-${String(month+1).padStart(2,'0')}-${String(d).padStart(2,'0')}">
                            <strong>${d}</strong>
                            <div class="weekday-name">${weekdayLabel}</div>
                            <div class="dots-wrap">${dots}</div>
                         </td>`;
                        day++;
                    }

                    while (day % 7 !== 0) {
                        html += `<td></td>`;
                        day++;
                    }

                    html += `</tr></tbody></table>`;
                    calendarEl.innerHTML = html;

                    document.querySelectorAll('#calendar td[data-date]').forEach(td => {
                        td.addEventListener('click', function() {
                            let date = this.dataset.date;
                            window.location.href =
                                `{{ route('admin.day_shifts') }}?date=${date}`;
                        });
                    });
                });
            }

            renderCalendar(current.getFullYear(), current.getMonth());
            document.getElementById('prev-month').onclick = () => {
                current.setMonth(current.getMonth() - 1);
                renderCalendar(current.getFullYear(), current.getMonth());
            };
            document.getElementById('next-month').onclick = () => {
                current.setMonth(current.getMonth() + 1);
                renderCalendar(current.getFullYear(), current.getMonth());
            };
        });
    </script>
@endsection

@section('style')
    <style>
        :root {
            --bg-dark: #1e1e1e;
            --card-dark: #2b2b2b;
            --input-dark: #3a3a3a;
            --border-dark: #555;
            --gold: #d4af37;
            --gold-hover: #b9962d;
            --text-light: #ffffff;
            --text-muted: #aaa;
        }

        body {
            background: var(--bg-dark);
            color: var(--text-light);
            font-family: "Cairo", sans-serif;
        }

        .card {
            background: var(--card-dark);
            border: 1px solid var(--border-dark);
            border-radius: 16px;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.6);
        }

        .theme-btn {
            background-color: var(--gold);
            color: #1e1e1e;
            font-weight: 700;
            border: none;
            border-radius: 10px;
            transition: 0.3s ease;
        }

        .theme-btn:hover {
            background-color: var(--gold-hover);
            color: #fff;
            transform: scale(1.05);
        }

        /* الكالندر */
        #calendar {
            background: var(--card-dark);
            border-radius: 14px;
            padding: 16px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.5);
        }

        #calendar table {
            width: 100%;
            border-collapse: collapse;
            color: var(--text-light);
        }

        #calendar td {
            background: var(--input-dark);
            color: var(--text-light);
            border-radius: 10px;
            padding: 10px;
            height: 90px;
            text-align: center;
            vertical-align: top;
            border: 1px solid var(--border-dark);
            transition: all 0.25s ease;
        }

        #calendar td:hover {
            background: rgba(212, 175, 55, 0.15);
            transform: scale(1.04);
            cursor: pointer;
        }

        #calendar td strong {
            display: block;
            color: var(--text-light);
            font-weight: 700;
            font-size: 15px;
            margin-bottom: 4px;
        }

        #calendar td.today {
            background: var(--gold);
            color: #1e1e1e;
            font-weight: bold;
        }

        .weekday-name {
            font-size: 0.85rem;
            color: var(--text-muted);
        }

        .booking-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: var(--gold);
            display: inline-block;
            margin: 2px;
            box-shadow: 0 0 4px var(--gold);
        }

        .text-muted {
            color: var(--text-muted) !important;
        }

        @media (max-width: 768px) {
            #calendar td {
                height: 65px;
                min-width: 45px;
                padding: 6px;
            }

            #calendar td strong {
                font-size: 13px;
            }
        }
    </style>
@endsection
