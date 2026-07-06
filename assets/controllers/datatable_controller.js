import { Controller } from '@hotwired/stimulus';
import { DataTable } from "simple-datatables";

export default class extends Controller {
    static values = { title: String };

    connect() {
        this.tableElement = this.element.tagName === 'TABLE' ? this.element : this.element.querySelector('table');
        const title = this.titleValue || '';

        if (this.tableElement) {
            this.dataTable = new DataTable(this.tableElement, {
                labels: {
                    placeholder: 'Rechercher...',
                    perPage: '{select} lignes par page',
                    noRows: 'Aucun résultat trouvé',
                },
                paging: true,
                perPage: 10,
                perPageSelect: [5, 10, 25, 50],
                searchable: true,
                sortable: true,
                template: (options, dom) => `
                    <div class="grid grid-cols-3 items-center m-4 gap-4">
                        <div></div>
                        <h2 class="text-2xl font-bold text-center">${title}</h2>
                        <div class="${options.classes.search} justify-self-end">
                            <input class="${options.classes.input} input input-bordered input-sm" placeholder="${options.labels.placeholder}" type="search">
                        </div>
                    </div>
                    <div class="${options.classes.container}"></div>
                    <div class="flex justify-between items-center m-4">
                        <div class="${options.classes.dropdown}">
                            <label class="flex items-center gap-2 text-sm">
                                <select class="${options.classes.selector} select select-bordered select-sm"></select>
                                lignes par page
                            </label>
                        </div>
                        <nav class="${options.classes.pagination}">
                            <ul class="${options.classes.paginationList}"></ul>
                        </nav>
                    </div>
                `
            });
        }
    }

    disconnect() {
        if (this.dataTable) {
            this.dataTable.destroy();
            this.dataTable = null;
        }
    }
}
