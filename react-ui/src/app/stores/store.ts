import { createContext, useContext } from "react";
import UserStore from "./userStore";
import ThreadStore from "./threadStore";
import PostStore from "./postStore";

interface Store {
    threadStore: ThreadStore;
    userStore: UserStore;
    postStore: PostStore;
}

export const store: Store = {
    threadStore: new ThreadStore(),
    postStore: new PostStore(),
    userStore: new UserStore(),
}

export const StoreContext = createContext(store);

export function useStore(){
    return useContext(StoreContext)
}