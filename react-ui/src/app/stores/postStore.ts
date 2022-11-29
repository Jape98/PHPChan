import { HubConnection, HubConnectionBuilder, LogLevel } from "@microsoft/signalr";
import { makeAutoObservable, runInAction } from "mobx";
import { ChatPost } from "../models/ChatPost";
import { store } from "./store";

export default class PostStore {
    posts: ChatPost[] = [];
    hubConnection: HubConnection | null = null;

    constructor() {
        makeAutoObservable(this);
    }

    createHubConnection = (threadId: string) => {
        //Took this off from .withUrl -> process.env.REACT_APP_CHAT_URL + '?threadId='
        if(store.threadStore.selectedThread) {
            this.hubConnection = new HubConnectionBuilder()
                .withUrl('http://localhost/controller/thread.php?threadId=' + threadId)
                .withAutomaticReconnect()
                .configureLogging(LogLevel.Information)
                .build();

            this.hubConnection.start().catch(error => console.log('Error establishing the connection: ', error));

            this.hubConnection.on('LoadPosts', (posts: ChatPost[]) => {
                runInAction(() => {
                    posts.forEach(post => {
                        post.createdAt = new Date(post.createdAt + 'Z');
                    })
                    this.posts = posts
                });
            });

            this.hubConnection.on('RecivePost', (post: ChatPost) => {
                runInAction(() => {
                    post.createdAt = new Date(post.createdAt);
                    this.posts.unshift(post)
                });
            });
        }   
    }

    stopHubConnection = () => {
        this.hubConnection?.stop().catch(error => console.log('Error stopping connection: ', error));
    }

    clearPosts = () => {
        this.posts = [];
        this.stopHubConnection();
    }

    addPost = async (values: any) => {
        values.threadId = store.threadStore.selectedThread?.id;

        try {
            await this.hubConnection?.invoke('SendPost', values)
        } catch (error) {
            console.log(error);
        }
    }
}