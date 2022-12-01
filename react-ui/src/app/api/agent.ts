import axios, { AxiosResponse } from "axios";
import { User, UserCreateValues, UserLoginValues } from "../models/User";
import { Thread, ThreadPostValues } from "../models/Thread";
import { Post } from "../models/Post";
import { store } from "../stores/store";
import { request } from "http";

axios.defaults.baseURL = "http://localhost/";


axios.interceptors.request.use(config => {
    const token = store.userStore.token;
    if (token && config.headers) config.headers.Authorization = `Bearer ${token}`
    
    return config;
})


const responseBody = <T> (response: AxiosResponse<T>) => response.data;

const requests = {
    get: <T>(url: string) => axios.get<T>(url).then(responseBody),
    post: <T>(url: string, body: {}) => axios.post<T>(url, body).then(responseBody),
    put: <T>(url: string, body: {}) => axios.put<T>(url, body).then(responseBody),
    delete: <T>(url: string) => axios.delete<T>(url).then(responseBody)
}

const Threads = {
    list: () => requests.get<Thread[]>("/controller/thread.php?"),
    details: (id: string) => requests.get<Thread[]>(`/controller/thread.php?id=${id}`),
    addnewThread: (thread: ThreadPostValues) => requests.post<User>('/controller/addNewThread', thread)
}

const Account = {
    current:() => requests.get<User>('/account/current'),
    login: (user: UserLoginValues) => requests.post<User>('/controller/users.php', user),
    refreshToken: () => requests.post<User>('account/current', {}),
    create: (user : UserCreateValues) => requests.post<User>('/controller/users.php', user)
}

const agent = {
    Threads,
    Account
}

export default agent;