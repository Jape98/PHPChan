export interface User {
    username: string;
    token: string;
}

export interface UserLoginValues {
    email: string;
    password: string;
}

export interface UserCreateValues {
        username: string,
        password: string,
        email: string
}