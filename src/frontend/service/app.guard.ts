
import { Injectable } from '@angular/core';
import { ActivatedRouteSnapshot, CanActivate, Router, RouterStateSnapshot } from '@angular/router';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';
import { map } from 'rxjs/operators';
import { HeaderService } from './header.service';

@Injectable({
    providedIn: 'root'
})
export class AppGuard implements CanActivate {

    constructor(public http: HttpClient,
        private router: Router,
        public headerService: HeaderService) { }

    canActivate(route: ActivatedRouteSnapshot, state: RouterStateSnapshot): Observable<boolean> | boolean {
        // TO DO : CAN BE REMOVE AFTER FULL V2
        localStorage.setItem('PreviousV2Route', state.url);

        if (this.headerService.user.id === undefined) {
            return this.http.get('../../rest/currentUser/profile')
                .pipe(
                    map((data: any) => {
                        this.headerService.user = {
                            id : data.id,
                            userId : data.user_id,
                            firstname : data.firstname,
                            lastname: data.lastname,
                            entities: data.entities,
                            groups: data.groups
                        }
                        return true;
                    })
                );
        } else {
            return true;
        }
    }
}
