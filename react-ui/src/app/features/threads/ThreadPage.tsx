import { observer } from "mobx-react-lite";
import React, { useEffect } from "react";
import { useParams } from "react-router-dom";
import { useStore } from "../../stores/store";
import ThreadChat from "./ThreadChat";

export default observer(function ThreadsPage() {
    const {threadStore} = useStore();
    const {selectedThread: thread, loadThread, clearSelecterThread} = threadStore;
    const {id} = useParams<{id: string}>();

    useEffect(() => {
        if (id) threadStore.loadThread(id);
        return () => clearSelecterThread();
    }, [id, loadThread, clearSelecterThread]);
    
    
    if(thread){
        return(<div>
                <ThreadChat  thread={thread} />
            </div>
        )
    } else {
        return(
            <div>invalid id</div>
        )  
    }
});