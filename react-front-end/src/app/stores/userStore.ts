import { makeAutoObservable, reaction, runInAction } from "mobx";
import { history } from "../..";
import agent from "../api/agent";
import { User, UserLoginValues } from "../models/User";


export default class UserStore {
    user: User | null = null;
    token: string | null = window.localStorage.getItem("jwt");
    refreshTokenTimeout: any;

    constructor() {
        makeAutoObservable(this);

        reaction(() => this.token, token => {
                if (token) {
                    window.localStorage.setItem("jwt", token)
                } else {
                    window.localStorage.removeItem("jwt")
                }
            }
        )
    }
    
    get isLoggedIn() {
        return !!this.user;
    }

    login = async (creds:UserLoginValues) => {
        try {
            const user = await agent.Account.login(creds);
            this.setToken(user.token);
            runInAction(()=> this.user = user);
            history.push("/")

        } catch (error) {
            throw error;
        }
    }

    getUser = async () => {
        try {
            const user = await agent.Account.current();
            this.setToken(user.token);
            runInAction(() => this.user = user);
        } catch (error) {
            console.log(error);
        }
    }
    
    logout = () => {

        this.setToken(null);
        window.localStorage.removeItem("jwt")
        this.user = null;
    }

    setToken = (token: string | null) => {
        this.token = token;
    }

    refreshToken = async () => {
        this.stopRefreshTokenTimer();
        try {
            const user = await agent.Account.refreshToken();
            runInAction (() => this.user = user);
            this.setToken(user.token);
            this.startRefreshTokenTimer(user);
        } catch (error) {
            console.log(error);
        }
    }

    private startRefreshTokenTimer(user: User){
        const jwtToken = JSON.parse(atob(user.token.split('.')[1]))
        const expires = new Date(jwtToken.exp * 1000);
        const timeout = expires.getTime() - Date.now() - (30*1000);
        this.refreshTokenTimeout = setTimeout(this.refreshToken, timeout);
    }

    private stopRefreshTokenTimer() {
        clearTimeout(this.refreshTokenTimeout);
    }
}