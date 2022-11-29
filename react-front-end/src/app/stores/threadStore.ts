import { makeAutoObservable, runInAction } from "mobx";
import agent from "../api/agent";
import { Thread, ThreadPostValues } from "../models/Thread";

export default class ThreadStore {
    threads: Thread[] = [];
    threadRegistry = new Map<string, Thread>();
    selectedThread: Thread | undefined = undefined;
    editMode = false;
    currentThread: Thread | undefined = undefined;

    constructor() {
        makeAutoObservable(this)
    }

    loadThreads = async () => {
        try {
            var thread = await agent.Threads.list();
            thread.forEach((element , index) => {
                this.threadRegistry.set(String((index + 1)),element)
            });
            runInAction(() => {this.threads = thread;})
            
        } catch (error) {
            runInAction(() => {console.log(error)})
        }
        
    }

    addNewThread = async (creds:ThreadPostValues) => {
        try {
            return "";

        } catch (error) {
            throw error;
            return "";
        }
    }

    loadThread = async (id: string) => {
        console.log(id);
        
        let thread = await agent.Threads.details(id);
        
        if (thread) {
            runInAction(() => this.selectedThread = thread[0]);
            
            return thread;
        } else {
            
            try{
                thread = await agent.Threads.details(id);
                this.setThread(thread[0]!);
                this.selectedThread = thread[0];
                return thread;
            }catch (error){
                console.log(error)
            }
        }
    }

    private getThread = (id: string) => {
        return this.threadRegistry.get(id);
    }

    private setThread = (thread: Thread) => {
        this.threadRegistry.set(thread.id, thread)
    }

    clearSelecterThread = () => {
        runInAction(() => this.selectedThread = undefined);
    }

    setCurrentThread = (id : string) => {
        runInAction(() => this.currentThread = this.selectedThread);
    }

    getCurrentThread = () =>{
        return this.currentThread;
    }

    removeCurrentThread = () =>{
        runInAction(() => this.currentThread = undefined);
    }
}