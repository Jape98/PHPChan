import React from "react";
import { Link } from "react-router-dom";
import { Button, Item, ItemContent, Segment } from "semantic-ui-react";
import { Thread } from "../../models/Thread";
import { useStore } from "../../stores/store";
import AddNewThread from "./AddNewThread";

interface Props {
    threads: Thread[];
}


export default function ThreadsView({threads}:Props) {
    return(
        <Segment>
            <Item.Group divided>
                        <Item>
                            <ItemContent>
                                <AddNewThread />
                            </ItemContent>
                        </Item>
            {threads.map( thread => (
                    <>
                        <Item key={thread.id}>
                            <ItemContent>
                                <Item.Header as="a">Thread {thread.id} by {thread.userName}</Item.Header>
                                < Item.Description content={thread.content}/>
                                <Item.Extra>
                                    <Button as={Link} to={`/thread/${thread.id}`} floated="right" content="Open thread" color="green"/>
                                </Item.Extra>
                            </ItemContent>
                        </Item>
                    </>
                ))}
            </Item.Group>
        </Segment>
    )  
}