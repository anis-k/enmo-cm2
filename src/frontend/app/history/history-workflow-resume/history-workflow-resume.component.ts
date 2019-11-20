import { Component, OnInit, Input, EventEmitter, Output } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { LANG } from '../../translate.component';
import { catchError, tap, finalize } from 'rxjs/operators';
import { of } from 'rxjs';
import { NotificationService } from '../../notification.service';
import { PrivilegeService } from '../../../service/privileges.service';


@Component({
    selector: 'app-history-workflow-resume',
    templateUrl: "history-workflow-resume.component.html",
    styleUrls: [
        'history-workflow-resume.component.scss',
    ]
})

export class HistoryWorkflowResumeComponent implements OnInit {

    lang: any = LANG;

    loading: boolean = true;

    histories: any[] = [];

    @Input('resId') resId: number = null;
    @Output('goTo') goTo = new EventEmitter<string>();

    constructor(
        public http: HttpClient,
        private notify: NotificationService,
        public privilegeService: PrivilegeService
    ) {
    }

    ngOnInit(): void {
        this.loading = true;
        this.loadHistory(this.resId);
    }

    loadHistory(resId: number) {
        this.loading = true;
        this.http.get(`../../rest/histories/resources/${resId}/workflow?limit=3`).pipe(
            tap((data: any) => {
                this.histories = data.history;
            }),
            finalize(() => this.loading = false),
            catchError((err: any) => {
                this.notify.handleErrors(err);
                return of(false);
            })
        ).subscribe();
    }

    showMore() {
        this.goTo.emit();
    }
}