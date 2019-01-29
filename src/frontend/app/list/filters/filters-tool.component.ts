import { Component, OnInit, ViewEncapsulation, Input, EventEmitter, Output, ViewChild } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { LANG } from '../../translate.component';
import { MatSidenav, MatMenu, MatMenuTrigger, MatAutocompleteSelectedEvent, MatInput, MatAutocompleteTrigger, MatDialog } from '@angular/material';
import { FiltersListService } from '../../../service/filtersList.service';
import { Observable } from 'rxjs';
import { FormBuilder, FormGroup } from '@angular/forms';
import { startWith, map } from 'rxjs/operators';
import { LatinisePipe } from 'ngx-pipes';
import { ListAdministrationComponent } from '../../administration/list/list-administration.component';
import { SummarySheetComponent } from '../summarySheet/summary-sheet.component';


declare function $j(selector: any): any;

export interface StateGroup {
    letter: string;
    names: any[];
}

@Component({
    selector: 'app-filters-tool',
    templateUrl: 'filters-tool.component.html',
    styleUrls: ['filters-tool.component.scss'],
    encapsulation: ViewEncapsulation.None,
    providers: [LatinisePipe],
})
export class FiltersToolComponent implements OnInit {

    lang: any = LANG;

    stateForm: FormGroup = this.fb.group({
        stateGroup: '',
    });

    displayColsOrder = [
        { 'id': 'dest_user' },
        { 'id': 'category_id' },
        { 'id': 'creation_date' },
        { 'id': 'process_limit_date' },
        { 'id': 'entity_label' },
        { 'id': 'subject' },
        { 'id': 'alt_identifier' },
        { 'id': 'priority' },
        { 'id': 'status' },
        { 'id': 'type_label' }
    ];

    @ViewChild(MatAutocompleteTrigger) autocomplete: MatAutocompleteTrigger;

    priorities: any[] = [];
    categories: any[] = [];
    entitiesList: any[] = [];
    statuses: any[] = [];
    metaSearchInput: string = '';

    stateGroups: StateGroup[] = [];
    stateGroupOptions: Observable<StateGroup[]>;

    isLoading: boolean = false;

    @Input('listProperties') listProperties: any;
    @Input('currentBasketInfo') currentBasketInfo: any;

    @Input('snavR') sidenavRight: MatSidenav;

    @Output('refreshEvent') refreshEvent = new EventEmitter<string>();

    constructor(public http: HttpClient, private filtersListService: FiltersListService, private fb: FormBuilder, private latinisePipe: LatinisePipe, public dialog: MatDialog) { }

    ngOnInit(): void {

    }

    private _filter = (opt: string[], value: string): string[] => {

        if (typeof value === 'string') {
            const filterValue = value.toLowerCase();
                
            return opt.filter(item => this.latinisePipe.transform(item['label'].toLowerCase()).indexOf(this.latinisePipe.transform(filterValue)) != -1);
        }
    };

    private _filterGroup(value: string): StateGroup[] {
        if (value && typeof value === 'string') {
            return this.stateGroups
                .map(group => ({ letter: group.letter, names: this._filter(group.names, value) }))
                .filter(group => group.names.length > 0);
        }

        return this.stateGroups;
    }

    changeOrderDir() {
        if (this.listProperties.orderDir == 'ASC') {
            this.listProperties.orderDir = 'DESC';
        } else {
            this.listProperties.orderDir = 'ASC';
        }
        this.updateFilters();
    }

    updateFilters() {
        this.listProperties.page = 0;

        this.filtersListService.updateListsProperties(this.listProperties);

        this.refreshEvent.emit();
    }

    setFilters(e: any, id: string) {
        this.listProperties[id] = e.source.checked;
        this.updateFilters();
    }


    selectFilter(e: MatAutocompleteSelectedEvent) {
        this.listProperties[e.option.value.id].push({
            'id': e.option.value.value,
            'label': e.option.value.label
        });
        $j('.metaSearch').blur();
        this.stateForm.controls['stateGroup'].reset();
        this.updateFilters();
    }

    metaSearch(e: any) {
        this.listProperties.search = e.target.value;
        $j('.metaSearch').blur();
        this.stateForm.controls['stateGroup'].reset();
        this.autocomplete.closePanel();
        this.updateFilters();
    }

    removeFilter(id: string, i: number) {
        this.listProperties[id].splice(i, 1);
        this.updateFilters();
    }

    removeFilters() {
        Object.keys(this.listProperties).forEach((key) => {
            if (Array.isArray(this.listProperties[key])) {
                this.listProperties[key] = [];
            } else if (key == 'search') {
                this.listProperties[key] = '';
            }
        });
        this.updateFilters();
    }

    haveFilters() {
        let state = false;
        Object.keys(this.listProperties).forEach((key) => {
            if ((Array.isArray(this.listProperties[key]) && this.listProperties[key].length > 0) || (key == 'search' && this.listProperties[key] != '')) {
                state = true;
            }
        });
        return state;
    }

    setInputSearch(value: string) {
        $j('.metaSearch').focus();
        this.metaSearchInput = value;
    }

    initFilters() {
        this.isLoading = true;

        this.stateForm.controls['stateGroup'].reset();
        this.stateGroups = [
            {
                letter: this.lang.categories,
                names: []
            },
            {
                letter: this.lang.priorities,
                names: []
            },
            {
                letter: this.lang.statuses,
                names: []
            },
            {
                letter: this.lang.entities,
                names: []
            },
            {
                letter: this.lang.subEntities,
                names: []
            },
        ];

        this.http.get('../../rest/resourcesList/users/' + this.currentBasketInfo.ownerId + '/groups/' + this.currentBasketInfo.groupId + '/baskets/' + this.currentBasketInfo.basketId + '/filters?init' + this.filtersListService.getUrlFilters())
            .subscribe((data: any) => {
                data.categories.forEach((element: any) => {
                    if (this.listProperties.categories.map((category: any) => (category.id)).indexOf(element.id) === -1) {
                        this.stateGroups[0].names.push(
                            {
                                id: 'categories',
                                value: element.id,
                                label: (element.id !== null ? element.label : this.lang.undefined) ,
                                count: element.count
                            }
                        )
                    }
                });
                data.priorities.forEach((element: any) => {
                    if (this.listProperties.priorities.map((priority: any) => (priority.id)).indexOf(element.id) === -1) {
                        this.stateGroups[1].names.push(
                            {
                                id: 'priorities',
                                value: element.id,
                                label: (element.id !== null ? element.label : this.lang.undefined) ,
                                count: element.count
                            }
                        )
                    }
                });
                data.statuses.forEach((element: any) => {
                    if (this.listProperties.statuses.map((status: any) => (status.id)).indexOf(element.id) === -1) {
                        this.stateGroups[2].names.push(
                            {
                                id: 'statuses',
                                value: element.id,
                                label: (element.id !== null ? element.label : this.lang.undefined) ,
                                count: element.count
                            }
                        )
                    }

                });

                data.entities.forEach((element: any) => {
                    if (this.listProperties.entities.map((entity: any) => (entity.id)).indexOf(element.entityId) === -1 && this.listProperties.subEntities == 0) {
                        this.stateGroups[3].names.push(
                            {
                                id: 'entities',
                                value: element.entityId,
                                label: (element.id !== null ? element.label : this.lang.undefined) ,
                                count: element.count
                            }
                        )
                    }

                });

                data.entitiesChildren.forEach((element: any) => {
                    if (this.listProperties.subEntities.map((entity: any) => (entity.id)).indexOf(element.entityId) === -1 && this.listProperties.entities == 0) {
                        this.stateGroups[4].names.push(
                            {
                                id: 'subEntities',
                                value: element.entityId,
                                label: (element.id !== null ? element.label : this.lang.undefined) ,
                                count: element.count
                            }
                        )
                    }
                });
                this.isLoading = false;
                if (this.metaSearchInput.length > 0) {
                    setTimeout(() => {
                        this.stateForm.controls['stateGroup'].setValue(this.metaSearchInput);
                        this.metaSearchInput = '';
                    }, 200);
                } 
            });

        this.stateGroupOptions = this.stateForm.get('stateGroup')!.valueChanges
            .pipe(
                startWith(''),
                map((value: any) => this._filterGroup(value))
            );
    }

    openListAdmin(): void {
        this.dialog.open(ListAdministrationComponent, {
          width: '800px',
          data: {
              ownerId   : this.currentBasketInfo.ownerId,
              groupId   : this.currentBasketInfo.groupId,
              basketId  : this.currentBasketInfo.basketId,
              filters   : this.filtersListService.getUrlFilters()
          }
        });
    }

    openSummarySheet(): void {
        this.dialog.open(SummarySheetComponent, {
          width: '800px',
          data: {
              ownerId   : this.currentBasketInfo.ownerId,
              groupId   : this.currentBasketInfo.groupId,
              basketId  : this.currentBasketInfo.basketId,
              filters   : this.filtersListService.getUrlFilters()
          }
        });
    }
}
